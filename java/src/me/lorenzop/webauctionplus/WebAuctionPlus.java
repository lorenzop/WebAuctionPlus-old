package me.lorenzop.webauctionplus;

import java.io.IOException;
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
import me.lorenzop.webauctionplus.mysql.MySQLDataQueries;
import me.lorenzop.webauctionplus.mysql.MySQLTables;
import me.lorenzop.webauctionplus.tasks.AnnouncerTask;
import me.lorenzop.webauctionplus.tasks.CronExecutorTask;
import me.lorenzop.webauctionplus.tasks.RecentSignTask;
import me.lorenzop.webauctionplus.tasks.SaleAlertTask;
import me.lorenzop.webauctionplus.tasks.ShoutSignTask;
import net.milkbowl.vault.economy.Economy;

import org.bukkit.ChatColor;
import org.bukkit.Location;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.entity.Player;
import org.bukkit.plugin.Plugin;
import org.bukkit.plugin.PluginManager;
import org.bukkit.plugin.java.JavaPlugin;
import org.bukkit.scheduler.BukkitScheduler;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

public class WebAuctionPlus extends JavaPlugin {

	// isDev is for testing mode only
	public final boolean isDev = true;
	private      boolean isOk  = false;

	public static String logPrefix  = "[WebAuction+] ";
	public static String chatPrefix = ChatColor.DARK_GREEN+"["+ChatColor.WHITE+"WebAuction+"+ChatColor.DARK_GREEN+"] ";
	public static final Logger log = Logger.getLogger("Minecraft");

	public static Metrics metrics;
	public static waStats Stats;

	// plugin version
	public static String currentVersion;
	public static String newVersion;

	// config
	public FileConfiguration Config;
	public static waSettings settings;

	// language
	public static Language Lang;

	public MySQLDataQueries dataQueries;
	public WebAuctionCommands WebAuctionCommandsListener = new WebAuctionCommands(this);
	public PlayerActions waPlayerActions = new PlayerActions(this);

	public Map<String,   Long>    lastSignUse = new HashMap<String , Long>();
	public Map<Location, Integer> recentSigns = new HashMap<Location, Integer>();
	public Map<Location, Integer> shoutSigns  = new HashMap<Location, Integer>();

//	public int totalAuctionCount	= 0;
	public int signDelay			= 0;
	public int numberOfRecentLink	= 0;

	// sign link
	public Boolean useSignLink		= false;

	// tim the enchanter
	public boolean timEnabled		= false;

	public Boolean useOriginalRecent= false;
	public Boolean showSalesOnJoin	= false;

	// cron executor
	public CronExecutorTask waCronExecutorTask;
	boolean cronExecutorEnabled		= false;

	// announcer
	public AnnouncerTask waAnnouncerTask;
	public boolean announceEnabled	= false;

	public Economy economy			= null;

	public WebAuctionPlus() {
	}

	public void onEnable() {
		isOk = false;
		currentVersion = getDescription().getVersion();

//		log.info(logPrefix + "WebAuctionPlus is initializing.");
		if(isDev) {
			getServer().getConsoleSender().sendMessage(ChatColor.RED+"******************************");
			getServer().getConsoleSender().sendMessage(ChatColor.RED+"*** Running in dev mode!!! ***");
			getServer().getConsoleSender().sendMessage(ChatColor.RED+"***    for testing only    ***");
			getServer().getConsoleSender().sendMessage(ChatColor.RED+"******************************");
		}

		// Command listener
		getCommand("wa").setExecutor(WebAuctionCommandsListener);

		// init configs
		Config = getConfig();
		initConfig();

		// connect MySQL
		if (!ConnectDB()) {
			log.severe(logPrefix+"*** Failed to load WebAuctionPlus. Please check your config.");
			onDisable();
			return;
		}
		Stats = new waStats(this);

		// load settings from db
		settings = new waSettings(this);
		settings.LoadSettings();
		if(!settings.isOk()) {onDisable(); return;}

		// update the version in db
		if(! currentVersion.equals(settings.getString("Version")) )
			settings.setString("Version", currentVersion);

		// load config.yml
		if(!onLoadConfig()) {onDisable(); return;}

		// load language file
		Lang = new Language(this);
		Lang.loadLanguage(settings.getString("Language"));
		if(!Lang.isOk()) {onDisable(); return;}

		onLoadMetrics();
		checkUpdateAvailable(this);

		PluginManager pm = getServer().getPluginManager();
		pm.registerEvents(new WebAuctionPlayerListener(this), this);
		pm.registerEvents(new WebAuctionBlockListener (this), this);
		pm.registerEvents(new WebAuctionServerListener(this), this);
		isOk = true;
	}
	public boolean isOk() {return this.isOk;}

	public boolean onLoadConfig() {
		try {
//			addComment("debug_mode", Arrays.asList("# This is where you enable debug mode"))
			showSalesOnJoin    = Config.getBoolean("Misc.ShowSalesOnJoin");
			signDelay          = Config.getInt    ("Misc.SignClickDelay");
			timEnabled         = Config.getBoolean("Misc.UnsafeEnchantments");
			useSignLink        = Config.getBoolean("SignLink.Enabled");
			numberOfRecentLink = Config.getInt    ("SignLink.NumberOfLatestAuctionsToTrack");

			// scheduled tasks
			BukkitScheduler scheduler = getServer().getScheduler();
			boolean UseMultithreads = Config.getBoolean("Development.UseMultithreads");
			if (UseMultithreads) log.info(logPrefix + "Using Multiple Threads");
			else                 log.info(logPrefix + "Using Single Thread");

			// cron executor
			cronExecutorEnabled = Config.getBoolean("CronExecutor.Enabled");
			long cronExecutorMinutes = 20 * 60 * Config.getLong("Tasks.CronExecutorMinutes");
			if (cronExecutorEnabled && cronExecutorMinutes>0) {
				waCronExecutorTask = new CronExecutorTask();
				waCronExecutorTask.setCronUrl(Config.getString("CronExecutor.Url"));
				// cron executor task (always multi-threaded)
				scheduler.scheduleAsyncRepeatingTask(this, waCronExecutorTask,
					(cronExecutorMinutes/2), cronExecutorMinutes);
				log.info(logPrefix + "Enabled Task: Cron Executor");
			}

			// announcer
			announceEnabled = Config.getBoolean("Announcer.Enabled");
			long announcerMinutes = 20 * 60 * Config.getLong("Tasks.AnnouncerMinutes");
			if (announceEnabled && announcerMinutes>0) {
				waAnnouncerTask = new AnnouncerTask(this);
				waAnnouncerTask.chatPrefix     = Config.getString ("Announcer.Prefix");
				waAnnouncerTask.announceRandom = Config.getBoolean("Announcer.Random");
				waAnnouncerTask.addMessages(     Config.getStringList("Announcements"));
				if (UseMultithreads)
					scheduler.scheduleAsyncRepeatingTask(this, waAnnouncerTask,
						(announcerMinutes/2), announcerMinutes);
				else
					scheduler.scheduleSyncRepeatingTask (this, waAnnouncerTask,
						(announcerMinutes/2), announcerMinutes);
				log.info(logPrefix + "Enabled Task: Announcer");
			}

			long saleAlertSeconds        = 20 * Config.getLong("Tasks.SaleAlertSeconds");
			long shoutSignUpdateSeconds  = 20 * Config.getLong("Tasks.ShoutSignUpdateSeconds");
			long recentSignUpdateSeconds = 20 * Config.getLong("Tasks.RecentSignUpdateSeconds");
			useOriginalRecent            = Config.getBoolean  ("Misc.UseOriginalRecentSigns");

			// Build shoutSigns map
			if (shoutSignUpdateSeconds > 0)
				shoutSigns.putAll(dataQueries.getShoutSignLocations());
			// Build recentSigns map
			if (recentSignUpdateSeconds > 0)
				recentSigns.putAll(dataQueries.getRecentSignLocations());

			// report sales to players
			if (saleAlertSeconds > 0) {
				if (UseMultithreads)
					scheduler.scheduleAsyncRepeatingTask(this, new SaleAlertTask(this),
						saleAlertSeconds, saleAlertSeconds);
				else
					scheduler.scheduleSyncRepeatingTask (this, new SaleAlertTask(this),
						saleAlertSeconds, saleAlertSeconds);
				log.info(logPrefix + "Enabled Task: Sale Alert");
			}
			// shout sign task
			if (shoutSignUpdateSeconds > 0) {
				if (UseMultithreads)
					scheduler.scheduleAsyncRepeatingTask(this, new ShoutSignTask(this),
						shoutSignUpdateSeconds+(shoutSignUpdateSeconds/2), shoutSignUpdateSeconds);
				else
					scheduler.scheduleSyncRepeatingTask (this, new ShoutSignTask(this),
						shoutSignUpdateSeconds+(shoutSignUpdateSeconds/2), shoutSignUpdateSeconds);
				log.info(logPrefix + "Enabled Task: Shout Sign");
			}
			// update recent signs
			if (recentSignUpdateSeconds > 0 && useOriginalRecent) {
				if (UseMultithreads)
					scheduler.scheduleAsyncRepeatingTask(this, new RecentSignTask(this),
						recentSignUpdateSeconds-(recentSignUpdateSeconds/2), recentSignUpdateSeconds);
				else
					scheduler.scheduleSyncRepeatingTask (this, new RecentSignTask(this),
						recentSignUpdateSeconds-(recentSignUpdateSeconds/2), recentSignUpdateSeconds);
				log.info(logPrefix + "Enabled Task: Recent Sign");
			}
		} catch (Exception e) {
			log.severe("Unable to load config");
			e.printStackTrace();
			return false;
		}
		return true;
	}

	public void onSaveConfig() {
	}

	public void onDisable() {
		try {
			getServer().getScheduler().cancelTasks(this);
			if(dataQueries != null)
				dataQueries.forceCloseConnections();
			log.info(logPrefix + "Disabled, bye for now :-)");
		} catch (Exception ignore) {}
	}

	// Init database
	public boolean ConnectDB() {
		if ( ((String)Config.getString("MySQL.Password")).equals("password123") )
			return false;
		log.info(logPrefix + "MySQL Initializing.");
		try {
			int port = Config.getInt("MySQL.Port");
			if(port < 1) port = Integer.valueOf(Config.getString("MySQL.Port"));
			if(port < 1) port = 3306;
			dataQueries = new MySQLDataQueries(this,
				Config.getString("MySQL.Host"),
				port,
				Config.getString("MySQL.Username"),
				Config.getString("MySQL.Password"),
				Config.getString("MySQL.Database"),
				Config.getString("MySQL.TablePrefix")
			);
			dataQueries.ConnPoolSizeWarn = Config.getInt("MySQL.ConnectionPoolSizeWarn");
			dataQueries.ConnPoolSizeHard = Config.getInt("MySQL.ConnectionPoolSizeHard");
			dataQueries.debugSQL         = Config.getBoolean("Development.DebugSQL");
			if(isDev) dataQueries.debugSQL = true;
			// create/update tables
			MySQLTables dbTables = new MySQLTables(this);
			if(!dbTables.isOk()) {
				log.severe(logPrefix+"Error loading db tables!");
				return false;
			}
			dbTables = null;
		} catch (Exception e) {
			if (e.getCause() instanceof SQLException) {
				log.severe(logPrefix + "Unable to connect to MySQL database.");
			}
			e.printStackTrace();
			return false;
		}
		return true;
	}

	private void initConfig() {
		Config.addDefault("MySQL.Host",						"localhost");
		Config.addDefault("MySQL.Username",					"minecraft");
		Config.addDefault("MySQL.Password",					"password123");
		Config.addDefault("MySQL.Port",						3306);
		Config.addDefault("MySQL.Database",					"minecraft");
		Config.addDefault("MySQL.TablePrefix",				"WA_");
		Config.addDefault("MySQL.ConnectionPoolSizeWarn",	5);
		Config.addDefault("MySQL.ConnectionPoolSizeHard",	10);
		Config.addDefault("Misc.ReportSales",				true);
		Config.addDefault("Misc.UseOriginalRecentSigns",	true);
		Config.addDefault("Misc.ShowSalesOnJoin",			true);
		Config.addDefault("Misc.SignClickDelay",			500);
		Config.addDefault("Misc.UnsafeEnchantments",		false);
		Config.addDefault("Tasks.SaleAlertSeconds",			20L);
		Config.addDefault("Tasks.ShoutSignUpdateSeconds",	20L);
		Config.addDefault("Tasks.RecentSignUpdateSeconds",	60L);
		Config.addDefault("Tasks.CronExecutorMinutes",		60L);
		Config.addDefault("Tasks.AnnouncerMinutes",			60L);
		Config.addDefault("SignLink.Enabled",				false);
		Config.addDefault("SignLink.NumberOfLatestAuctionsToTrack", 10);
		Config.addDefault("Development.UseMultithreads",	false);
		Config.addDefault("Development.DebugSQL",			false);
		Config.addDefault("CronExecutor.Enabled",			false);
		Config.addDefault("CronExecutor.Url", "http://yourminecraftserver.com/webauctionplus/cron.php");
		Config.addDefault("Announcer.Enabled",				false);
		Config.addDefault("Announcer.Prefix",				"&c[Info] ");
		Config.addDefault("Announcer.Random",				false);
		Config.addDefault("Announcements", new String[]{"This server is running WebAuctionPlus!"} );
		Config.options().copyDefaults(true);
		saveConfig();
	}

	@SuppressWarnings("deprecation")
	public void doUpdateInventory(Player p) {
		p.updateInventory();
	}

	public long getCurrentMilli() {
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
	public static String FormatDouble(double value) {
		DecimalFormat decim = new DecimalFormat("0.00");
		return decim.format(value);
	}
	public static double ParseDouble(String value) {
		return Double.parseDouble( value.replaceAll("[^0-9.]+","") );
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

	public void onLoadMetrics() {
		log.info(logPrefix+"Starting metrics");
		// usage stats
		try {
			Metrics.isDev = isDev;
			metrics = new Metrics(this);
			// Create graphs for total Buy Nows / Auctions
			Metrics.Graph lineGraph = metrics.createGraph("Stacks For Sale");
			Metrics.Graph pieGraph  = metrics.createGraph("Selling Method");
			// buy now count
			Metrics.Plotter plotterBuyNows = new Metrics.Plotter("Buy Nows") {
				@Override
				public int getValue(){
					Stats.Update();
					return Stats.totalBuyNowCount;
				}
			};
			// auction count
			Metrics.Plotter plotterAuctions = new Metrics.Plotter("Auctions") {
				@Override
				public int getValue(){
					Stats.Update();
					return Stats.totalAuctionCount;
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
			if(isDev) {
				log.severe(e.getMessage());
				e.printStackTrace();
			}
		}
	}

	// updateCheck() from MilkBowl's Vault
	// modified for my compareVersions() function
	public static String updateCheck() throws Exception {
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
	public static boolean checkUpdateAvailable(Plugin plugin) {
		plugin.getServer().getScheduler().scheduleAsyncDelayedTask(plugin, new Runnable() {
			@Override
			public void run() {
				try {
					newVersion = updateCheck();
					String cmp = compareVersions(currentVersion, newVersion);
					if(cmp == "<") {
						log.warning(logPrefix+"An update is available!");
						log.warning(logPrefix+"You're running "+currentVersion+" new version available is "+newVersion);
						log.warning(logPrefix+"http://dev.bukkit.org/server-mods/webauctionplus");
					}
				} catch (Exception ignored) {}
			}
		}, 100);
		return false;
	}


}