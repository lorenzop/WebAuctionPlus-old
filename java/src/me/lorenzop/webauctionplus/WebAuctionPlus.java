package me.lorenzop.webauctionplus;

import java.io.IOException;
import java.math.BigDecimal;
import java.net.URL;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.sql.SQLException;
import java.text.DecimalFormat;
import java.util.HashMap;
import java.util.Map;
import java.util.Random;
import java.util.logging.Logger;
import java.util.regex.Pattern;

import javax.xml.parsers.DocumentBuilderFactory;

import me.lorenzop.webauctionplus.dao.waStats;
import me.lorenzop.webauctionplus.listeners.WebAuctionBlockListener;
import me.lorenzop.webauctionplus.listeners.WebAuctionCommands;
import me.lorenzop.webauctionplus.listeners.WebAuctionPlayerListener;
import me.lorenzop.webauctionplus.listeners.WebAuctionServerListener;
import me.lorenzop.webauctionplus.mysql.DataQueries;
import me.lorenzop.webauctionplus.mysql.MySQLPool;
import me.lorenzop.webauctionplus.mysql.MySQLPoolConn;
import me.lorenzop.webauctionplus.mysql.MySQLTables;
import me.lorenzop.webauctionplus.mysql.MySQLUpdate;
import me.lorenzop.webauctionplus.tasks.AnnouncerTask;
import me.lorenzop.webauctionplus.tasks.PlayerAlertTask;
import me.lorenzop.webauctionplus.tasks.RecentSignTask;
import me.lorenzop.webauctionplus.tasks.ShoutSignTask;
import net.milkbowl.vault.economy.Economy;

import org.bukkit.Bukkit;
import org.bukkit.ChatColor;
import org.bukkit.Location;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.entity.Player;
import org.bukkit.plugin.PluginManager;
import org.bukkit.plugin.java.JavaPlugin;
import org.bukkit.scheduler.BukkitScheduler;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

public class WebAuctionPlus extends JavaPlugin {

	private static boolean isOk    = false;
	private static boolean isDebug = false;

	public static final String logPrefix  = "[WebAuction+] ";
	public static final String chatPrefix = ChatColor.DARK_GREEN+"["+ChatColor.WHITE+"WebAuction+"+ChatColor.DARK_GREEN+"] ";
	public static final Logger log = Logger.getLogger("Minecraft");

	public static Metrics metrics;
	public static waStats Stats;

	// plugin version
	public static String currentVersion = null;
	public static String newVersion = null;
	public static boolean newVersionAvailable = false;

	// config
	public FileConfiguration config = null;
	public static waSettings settings = null;

	// language
	public static Language Lang;

	public static MySQLPool dbPool = null;
	public WebAuctionCommands WebAuctionCommandsListener = new WebAuctionCommands(this);

	public Map<String,   Long>    lastSignUse = new HashMap<String , Long>();
	public Map<Location, Integer> recentSigns = new HashMap<Location, Integer>();
	public Map<Location, Integer> shoutSigns  = new HashMap<Location, Integer>();

	public int signDelay			= 0;
	public int numberOfRecentLink	= 0;

	// use recent signs
	private static boolean useOriginalRecent = false;
	// sign link
	private static boolean useSignLink = false;
	// tim the enchanter
	private static boolean timEnabled = false;
	// globally announce new auctions (vs using shout signs)
	private static boolean announceGlobal = false;

	// JSON Server
//	public waJSONServer jsonServer;

	// recent sign task
	public static RecentSignTask recentSignTask = null;

	// announcer
	public AnnouncerTask waAnnouncerTask = null;
	public boolean announcerEnabled	= false;

	public static Economy vaultEconomy = null;


	public WebAuctionPlus() {
	}


	public void onEnable() {
		if(isOk) {
			getServer().getConsoleSender().sendMessage(ChatColor.RED+"********************************************");
			getServer().getConsoleSender().sendMessage(ChatColor.RED+"*** WebAuctionPlus is already running!!! ***");
			getServer().getConsoleSender().sendMessage(ChatColor.RED+"********************************************");
			return;
		}
		isOk = false;
		currentVersion = getDescription().getVersion();

		// Command listener
		getCommand("wa").setExecutor(WebAuctionCommandsListener);

		// load config.yml
		if(!onLoadConfig()) {onDisable(); return;}

		// load more services
		onLoadMetrics();
		checkUpdateAvailable();

		PluginManager pm = getServer().getPluginManager();
		pm.registerEvents(new WebAuctionPlayerListener(this), this);
		pm.registerEvents(new WebAuctionBlockListener (this), this);
		pm.registerEvents(new WebAuctionServerListener(),     this);
		isOk = true;
	}


	public void onDisable() {
		isOk = false;
		// stop schedulers
		try {
			getServer().getScheduler().cancelTasks(this);
		} catch (Exception ignore) {}
		if(waAnnouncerTask != null) waAnnouncerTask.clearMessages();
		if(shoutSigns      != null) shoutSigns.clear();
		if(recentSigns     != null) recentSigns.clear();
		// close inventories
		WebInventory.ForceCloseAll();
		// close mysql connection
		try {
			if(dbPool != null) dbPool.forceCloseConnections();
		} catch (Exception ignore) {}
		log.info(logPrefix + "Disabled, bye for now :-)");
		// close config
		try {
			if(config != null) config = null;
		} catch (Exception ignore) {}
		settings = null;
		Lang = null;
	}


	public void onReload() {
		onDisable();
		// load config.yml
		if(!onLoadConfig()) return;
		isOk = true;
	}


	public static boolean isOk()    {return isOk;}
	public static boolean isDebug() {return isDebug;}


	public boolean onLoadConfig() {
		// init configs
		if(config != null) config = null;
		config = getConfig();
		configDefaults();

		// connect MySQL
		if(dbPool == null)
			if(!ConnectDB()) {
				log.severe(logPrefix+"*** Failed to load WebAuctionPlus. Please check your config.");
				onDisable();
				return false;
			}

		// load stats class
		if(Stats == null) Stats = new waStats();

		// load settings from db
		if(settings != null) settings = null;
		settings = new waSettings(this);
		settings.LoadSettings();
		if(!settings.isOk()) {onDisable(); return false;}

		// update the version in db
		if(! currentVersion.equals(settings.getString("Version")) ){
			String oldVersion = settings.getString("Version");
			// update database
			MySQLUpdate.doUpdate(oldVersion);
			// update version number
			settings.setString("Version", currentVersion);
			log.info(logPrefix+"Updated version from "+oldVersion+" to "+currentVersion);
		}

		// load language file
		if(Lang != null) Lang = null;
		Lang = new Language(this);
		Lang.loadLanguage(settings.getString("Language"));
		if(!Lang.isOk()) {onDisable(); return false;}

		try {
			isDebug = config.getBoolean("Development.Debug");
//			addComment("debug_mode", Arrays.asList("# This is where you enable debug mode"))
			signDelay          = config.getInt    ("Misc.SignClickDelay");
			timEnabled         = config.getBoolean("Misc.UnsafeEnchantments");
			announceGlobal     = config.getBoolean("Misc.AnnounceGlobally");
			numberOfRecentLink = config.getInt    ("SignLink.NumberOfLatestAuctionsToTrack");
			useSignLink        = config.getBoolean("SignLink.Enabled");
			if(useSignLink)
				if(!Bukkit.getPluginManager().getPlugin("SignLink").isEnabled()) {
					log.warning(logPrefix+"SignLink is enabled but plugin is not loaded!");
					useSignLink = false;
				}

			// scheduled tasks
			BukkitScheduler scheduler = Bukkit.getScheduler();
			boolean UseMultithreads = config.getBoolean("Development.UseMultithreads");

			// announcer
			announcerEnabled = config.getBoolean("Announcer.Enabled");
			long announcerMinutes = 20 * 60 * config.getLong("Tasks.AnnouncerMinutes");
			if(announcerEnabled) waAnnouncerTask = new AnnouncerTask(this);
			if (announcerEnabled && announcerMinutes>0) {
				if(announcerMinutes < 6000) announcerMinutes = 6000; // minimum 5 minutes
				waAnnouncerTask.chatPrefix     = config.getString ("Announcer.Prefix");
				waAnnouncerTask.announceRandom = config.getBoolean("Announcer.Random");
				waAnnouncerTask.addMessages(     config.getStringList("Announcements"));
				scheduler.scheduleAsyncRepeatingTask(this, waAnnouncerTask,
					(announcerMinutes/2), announcerMinutes);
				log.info(logPrefix + "Enabled Task: Announcer (always multi-threaded)");
			}

			long saleAlertSeconds        = 20 * config.getLong("Tasks.SaleAlertSeconds");
			long shoutSignUpdateSeconds  = 20 * config.getLong("Tasks.ShoutSignUpdateSeconds");
			long recentSignUpdateSeconds = 20 * config.getLong("Tasks.RecentSignUpdateSeconds");
			useOriginalRecent            =      config.getBoolean("Misc.UseOriginalRecentSigns");

			// Build shoutSigns map
			if (shoutSignUpdateSeconds > 0)
				shoutSigns.putAll(DataQueries.getShoutSignLocations());
			// Build recentSigns map
			if (recentSignUpdateSeconds > 0)
				recentSigns.putAll(DataQueries.getRecentSignLocations());

			// report sales to players (always multi-threaded)
			if (saleAlertSeconds > 0) {
				if(saleAlertSeconds < 3*20) saleAlertSeconds = 3*20;
				scheduler.scheduleAsyncRepeatingTask(this, new PlayerAlertTask(),
					saleAlertSeconds, saleAlertSeconds);
				log.info(logPrefix + "Enabled Task: Sale Alert (always multi-threaded)");
			}
			// shout sign task
			if (shoutSignUpdateSeconds > 0) {
				if (UseMultithreads)
					scheduler.scheduleAsyncRepeatingTask(this, new ShoutSignTask(this),
						shoutSignUpdateSeconds, shoutSignUpdateSeconds);
				else
					scheduler.scheduleSyncRepeatingTask (this, new ShoutSignTask(this),
						shoutSignUpdateSeconds, shoutSignUpdateSeconds);
				log.info(logPrefix + "Enabled Task: Shout Sign (using " + (UseMultithreads?"multiple threads":"single thread") + ")");
			}
			// update recent signs
			if(recentSignUpdateSeconds > 0 && useOriginalRecent) {
				recentSignTask = new RecentSignTask(this);
				if (UseMultithreads)
					scheduler.scheduleAsyncRepeatingTask(this, recentSignTask,
						5*20, recentSignUpdateSeconds);
				else
					scheduler.scheduleSyncRepeatingTask (this, recentSignTask,
						5*20, recentSignUpdateSeconds);
				log.info(logPrefix + "Enabled Task: Recent Sign (using " + (UseMultithreads?"multiple threads":"single thread") + ")");
			}
		} catch (Exception e) {
			log.severe("Unable to load config");
			e.printStackTrace();
			return false;
		}
		return true;
	}


	// Init database
	public synchronized boolean ConnectDB() {
		if(config.getString("MySQL.Password").equals("password123"))
			return false;
		log.info(logPrefix + "MySQL Initializing.");
		if(dbPool != null) {
			log.severe("Database connection already made?!");
			return false;
		}
		try {
			int port = config.getInt("MySQL.Port");
			if(port < 1) port = Integer.valueOf(config.getString("MySQL.Port"));
			if(port < 1) port = 3306;
			dbPool = new MySQLPool(log, logPrefix,
				config.getString("MySQL.Host"),
				port,
				config.getString("MySQL.Username"),
				config.getString("MySQL.Password"),
				config.getString("MySQL.Database"),
				config.getString("MySQL.TablePrefix")
			);
			dbPool.setConnPoolSize_Warn(config.getInt("MySQL.ConnectionPoolSizeWarn"));
			dbPool.setConnPoolSize_Hard(config.getInt("MySQL.ConnectionPoolSizeHard"));
			// try connecting
			MySQLPoolConn poolConn = dbPool.getLock();
			if(poolConn == null) return false;
			poolConn.releaseLock();
			poolConn = null;
			// create/update tables
			MySQLTables dbTables = new MySQLTables();
			if(!dbTables.isOk()) {
				log.severe(logPrefix+"Error loading db updater class!");
				return false;
			}
			dbTables = null;
		} catch (Exception e) {
			if (e.getCause() instanceof SQLException)
				log.severe(logPrefix + "Unable to connect to MySQL database.");
			e.printStackTrace();
			return false;
		}
		return true;
	}


	private void configDefaults() {
		config.addDefault("MySQL.Host",						"localhost");
		config.addDefault("MySQL.Username",					"minecraft");
		config.addDefault("MySQL.Password",					"password123");
		config.addDefault("MySQL.Port",						3306);
		config.addDefault("MySQL.Database",					"minecraft");
		config.addDefault("MySQL.TablePrefix",				"WA_");
		config.addDefault("MySQL.ConnectionPoolSizeWarn",	5);
		config.addDefault("MySQL.ConnectionPoolSizeHard",	10);
		config.addDefault("Misc.ReportSales",				true);
		config.addDefault("Misc.UseOriginalRecentSigns",	true);
		config.addDefault("Misc.SignClickDelay",			500);
		config.addDefault("Misc.UnsafeEnchantments",		false);
		config.addDefault("Misc.AnnounceGlobally",			true);
		config.addDefault("Tasks.SaleAlertSeconds",			20L);
		config.addDefault("Tasks.ShoutSignUpdateSeconds",	20L);
		config.addDefault("Tasks.RecentSignUpdateSeconds",	60L);
		config.addDefault("Tasks.AnnouncerMinutes",			60L);
		config.addDefault("SignLink.Enabled",				false);
		config.addDefault("SignLink.NumberOfLatestAuctionsToTrack", 10);
		config.addDefault("Development.UseMultithreads",	false);
		config.addDefault("Development.Debug",				false);
		config.addDefault("Announcer.Enabled",				false);
		config.addDefault("Announcer.Prefix",				"&c[Info] ");
		config.addDefault("Announcer.Random",				false);
		config.addDefault("Announcements", new String[]{"This server is running WebAuctionPlus!"} );
		config.options().copyDefaults(true);
		saveConfig();
	}


	public static boolean useOriginalRecent() {
		return useOriginalRecent;
	}
	public static boolean useSignLink() {
		return useSignLink;
	}
	public static boolean timEnabled() {
		return timEnabled;
	}
	public static boolean announceGlobal() {
		return announceGlobal;
	}


	@SuppressWarnings("deprecation")
	public static synchronized void doUpdateInventory(Player p) {
		p.updateInventory();
	}


	public static long getCurrentMilli() {
		return System.currentTimeMillis();
	}


	// format chat colors
	public static String ReplaceColors(String text){
		return text.replaceAll("&([0-9a-fA-F])", "\247$1");
	}


	// add strings with delimiter
	public static String addStringSet(String baseString, String addThis, String Delim) {
		if (addThis.isEmpty())    return baseString;
		if (baseString.isEmpty()) return addThis;
		return baseString + Delim + addThis;
	}


//	public static String format(double amount) {
//		DecimalFormat formatter = new DecimalFormat("#,##0.00");
//		String formatted = formatter.format(amount);
//		if (formatted.endsWith("."))
//			formatted = formatted.substring(0, formatted.length() - 1);
//		return Common.formatted(formatted, Constants.Nodes.Major.getStringList(), Constants.Nodes.Minor.getStringList());
//	}


	// work with doubles
	public static String FormatPrice(double value) {
		return settings.getString("Currency Prefix") + FormatDouble(value) + settings.getString("Currency Postfix");
	}
	public static String FormatDouble(double value) {
		DecimalFormat decim = new DecimalFormat("##,###,##0.00");
		return decim.format(value);
	}
	public static double ParseDouble(String value) {
		return Double.parseDouble( value.replaceAll("[^0-9.]+","") );
	}
	public static double RoundDouble(double value, int precision, int roundingMode) {
		BigDecimal bd = new BigDecimal(value);
		BigDecimal rounded = bd.setScale(precision, roundingMode);
		return rounded.doubleValue();
	}


	public static int getNewRandom(int oldNumber, int maxNumber) {
		if (maxNumber == 0) return maxNumber;
		if (maxNumber == 1) return 1 - oldNumber;
		Random randomGen = new Random();
		int newNumber = 0;
		while (true) {
			newNumber = randomGen.nextInt(maxNumber + 1);
			if (newNumber != oldNumber) return newNumber;
		}
	}


	// min/max value
	public static int MinMax(int value, int min, int max) {
		if(value < min) value = min;
		if(value > max) value = max;
		return value;
	}
	public static long MinMax(long value, long min, long max) {
		if(value < min) value = min;
		if(value > max) value = max;
		return value;
	}
	public static double MinMax(double value, double min, double max) {
		if(value < min) value = min;
		if(value > max) value = max;
		return value;
	}
	// min/max by object
	public static boolean MinMax(Integer value, int min, int max) {
		boolean changed = false;
		if(value < min) {value = min; changed = true;}
		if(value > max) {value = max; changed = true;}
		return changed;
	}
	public static boolean MinMax(Long value, long min, long max) {
		boolean changed = false;
		if(value < min) {value = min; changed = true;}
		if(value > max) {value = max; changed = true;}
		return changed;
	}
	public static boolean MinMax(Double value, double min, double max) {
		boolean changed = false;
		if(value < min) {value = min; changed = true;}
		if(value > max) {value = max; changed = true;}
		return changed;
	}


	public static String MD5(String str) {
		MessageDigest md = null;
		try {
			md = MessageDigest.getInstance("MD5");
		} catch (NoSuchAlgorithmException e) {
			e.printStackTrace();
		}
		md.update(str.getBytes());
		byte[] byteData = md.digest();
		StringBuffer hexString = new StringBuffer();
		for (int i = 0; i < byteData.length; i++) {
			String hex = Integer.toHexString(0xFF & byteData[i]);
			if (hex.length() == 1) {
				hexString.append('0');
			}
			hexString.append(hex);
		}
		return hexString.toString();
	}


	public static void PrintProgress(double progress, int width) {
		String output = "[";
		int prog = (int)(progress * width);
		if (prog > width) prog = width;
		int i = 0;
		for (; i < prog; i++) {
			output += ".";
		}
		for (; i < width; i++) {
			output += " ";
		}
		WebAuctionPlus.log.info(output + "]");
	}
	public static void PrintProgress(int count, int total, int width) {
		try {
			// finished 100%
			if (count == total)
				PrintProgress( 1D, width);
			// total to small - skip
			else if (total < (width / 2) ) {}
			// print only when adding a .
			else if ( (int)(count % (total / width)) == 0)
				PrintProgress( (double)count / (double)total, width);
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	public static void PrintProgress(int count, int total) {
		PrintProgress(count, total, 20);
	}


	// announce radius
	public static void BroadcastRadius(String msg, Location loc, int radius) {
		Player[] playerList = Bukkit.getOnlinePlayers();
		Double x = loc.getX();
		Double z = loc.getZ();
		for(Player player : playerList) {
			Double playerX = player.getLocation().getX();
			Double playerZ = player.getLocation().getZ();
			if( (playerX < x + (double)radius ) &&
				(playerX > x - (double)radius ) &&
				(playerZ < z + (double)radius ) &&
				(playerZ > z - (double)radius ) )
					player.sendMessage(WebAuctionPlus.chatPrefix+msg);
		}
	}


	public void onLoadMetrics() {
		// usage stats
		try {
			metrics = new Metrics(this);
			metrics.setBaseUrl("http://metrics.poixson.com");
			if(metrics.isOptOut()) {
				log.info(logPrefix+"Plugin metrics are disabled, you bum");
				return;
			}
			log.info(logPrefix+"Starting metrics");
			// Create graphs for total Buy Nows / Auctions
			Metrics.Graph lineGraph = metrics.createGraph("Stacks For Sale");
			Metrics.Graph pieGraph  = metrics.createGraph("Selling Method");
			// buy now count
			Metrics.Plotter plotterBuyNows = new Metrics.Plotter("Buy Nows") {
				@Override
				public int getValue(){
					return Stats.getTotalBuyNows();
				}
			};
			// auction count
			Metrics.Plotter plotterAuctions = new Metrics.Plotter("Auctions") {
				@Override
				public int getValue(){
					return Stats.getTotalAuctions();
				}
			};
			// total selling
			lineGraph.addPlotter(plotterBuyNows);
			lineGraph.addPlotter(plotterAuctions);
			// selling ratio
			pieGraph.addPlotter(plotterBuyNows);
			pieGraph.addPlotter(plotterAuctions);
			metrics.start();
		} catch (IOException e) {
			// Failed to submit the stats :-(
			if(WebAuctionPlus.isDebug) {
				log.severe(e.getMessage());
				e.printStackTrace();
			}
		}
	}


	// updateCheck() from MilkBowl's Vault
	// modified for my compareVersions() function
	private static String doUpdateCheck() throws Exception {
		String pluginUrlString = "http://dev.bukkit.org/server-mods/webauctionplus/files.rss";
		try {
			URL url = new URL(pluginUrlString);
			Document doc = DocumentBuilderFactory.newInstance().newDocumentBuilder().parse(url.openConnection().getInputStream());
			doc.getDocumentElement().normalize();
			NodeList nodes = doc.getElementsByTagName("item");
			Node firstNode = nodes.item(0);
			if (firstNode.getNodeType() == 1) {
				Element firstElement = (Element) firstNode;
				NodeList firstElementTagName = firstElement.getElementsByTagName("title");
				Element firstNameElement = (Element) firstElementTagName.item(0);
				NodeList firstNodes = firstNameElement.getChildNodes();
				String version = firstNodes.item(0).getNodeValue();
				return version.substring(version.lastIndexOf(" ")+1);
			}
		} catch (Exception ignored) {}
		return null;
	}


	// compare versions
	public static String compareVersions(String oldVersion, String newVersion) {
		if(oldVersion == null || newVersion == null) return null;
		oldVersion = normalisedVersion(oldVersion);
		newVersion = normalisedVersion(newVersion);
		int cmp = oldVersion.compareTo(newVersion);
		return cmp<0 ? "<" : cmp>0 ? ">" : "=";
	}
	public static String normalisedVersion(String version) {
		String delim = ".";
		int maxWidth = 5;
		String[] split = Pattern.compile(delim, Pattern.LITERAL).split(version);
		String output = "";
		for(String s : split) {
			output += String.format("%"+maxWidth+'s', s);
		}
		return output;
	}


	// check for an updated version
	private void checkUpdateAvailable() {
		getServer().getScheduler().scheduleAsyncRepeatingTask(this, new Runnable() {
			@Override
			public void run() {
				try {
					newVersion = doUpdateCheck();
					String cmp = compareVersions(currentVersion, newVersion);
					if(cmp == "<") {
						newVersionAvailable = true;
						log.warning(logPrefix+"An update is available!");
						log.warning(logPrefix+"You're running "+currentVersion+" new version available is "+newVersion);
						log.warning(logPrefix+"http://dev.bukkit.org/server-mods/webauctionplus");
					}
				} catch (Exception ignored) {}
			}
		}, 5 * 20, 14400 * 20); // run every 4 hours
	}


}