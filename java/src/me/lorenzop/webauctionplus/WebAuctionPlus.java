package me.lorenzop.webauctionplus;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;
import java.util.Random;
import java.util.logging.Logger;

import me.exote.webauctionplus.WebAuctionCommands;
import me.exote.webauctionplus.listeners.WebAuctionBlockListener;
import me.exote.webauctionplus.listeners.WebAuctionPlayerListener;
import me.exote.webauctionplus.listeners.WebAuctionServerListener;
import me.exote.webauctionplus.tasks.RecentSignTask;
import me.exote.webauctionplus.tasks.SaleAlertTask;
import me.exote.webauctionplus.tasks.ShoutSignTask;
import me.lorenzop.webauctionplus.tasks.AnnouncerTask;
import me.lorenzop.webauctionplus.tasks.CronExecutorTask;
import net.milkbowl.vault.economy.Economy;
import net.milkbowl.vault.permission.Permission;

import org.bukkit.ChatColor;
import org.bukkit.Location;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.plugin.PluginManager;
import org.bukkit.plugin.RegisteredServiceProvider;
import org.bukkit.plugin.java.JavaPlugin;
import org.bukkit.scheduler.BukkitScheduler;

public class WebAuctionPlus extends JavaPlugin {

	public String logPrefix  = "[WebAuction+] ";
	public String chatPrefix = ChatColor.DARK_GREEN + "[" + ChatColor.WHITE + "WebAuction+" + ChatColor.DARK_GREEN + "] ";
	public Logger log = Logger.getLogger("Minecraft");

	public Double currentVersion;
	public Double newVersion;

	public MySQLDataQueries dataQueries;
	public WebAuctionCommands WebAuctionCommandsListener = new WebAuctionCommands(this);
	public PlayerActions waPlayerActions = new PlayerActions(this);

	public Map<String,   Long>    lastSignUse = new HashMap<String , Long>();
	public Map<Location, Integer> recentSigns = new HashMap<Location, Integer>();
	public Map<Location, Integer> shoutSigns  = new HashMap<Location, Integer>();

	public int signDelay             = 0;
	public int numberOfRecentLink    = 0;

	// sign link
	public Boolean useSignLink       = false;

	// tim the enchanter
	public boolean timEnabled        = false;

	public Boolean useOriginalRecent = false;
	public Boolean showSalesOnJoin   = false;

	// cron executor
	public CronExecutorTask waCronExecutorTask;
	boolean cronExecutorEnabled      = false;

	// announcer
	public AnnouncerTask waAnnouncerTask;
	public boolean announceEnabled   = false;

	public Permission permission     = null;
	public Economy    economy        = null;

	public WebAuctionPlus() {
	}

	public long getCurrentMilli() {
		return System.currentTimeMillis();
	}

	public void onEnable() {
		//currentVersion = Double.valueOf(getDescription().getVersion().split("-")[0].replaceFirst("\\.", ""));
		//newVersion = updateCheck(0.64D);
		//log.info("CURRENT: " + currentVersion.toString());
		//log.info("NEW" + newVersion.toString());

		log.info(logPrefix + "WebAuctionPlus is initializing.");

		// Command listener
		getCommand("wa").setExecutor(WebAuctionCommandsListener);

		// load config.yml
		onLoadConfig();

		setupEconomy();
		setupPermissions();
		PluginManager pm = getServer().getPluginManager();
		pm.registerEvents(new WebAuctionPlayerListener(this), this);
		pm.registerEvents(new WebAuctionBlockListener (this), this);
		pm.registerEvents(new WebAuctionServerListener(this), this);
	}

	public void onLoadConfig() {
		// Init configs
		initConfig();
		FileConfiguration Config = getConfig();
		showSalesOnJoin    = Config.getBoolean("Misc.ShowSalesOnJoin");
		signDelay          = Config.getInt    ("Misc.SignClickDelay");
		timEnabled         = Config.getBoolean("Misc.UnsafeEnchantments");
		useSignLink        = Config.getBoolean("SignLink.Enabled");
		numberOfRecentLink = Config.getInt    ("SignLink.NumberOfLatestAuctionsToTrack");

		// connect MySQL
		ConnectDB();

		// scheduled tasks
		BukkitScheduler scheduler = getServer().getScheduler();
		boolean UseMultithreads      = Config.getBoolean  ("Development.UseMultithreads");
		if (UseMultithreads) log.info(logPrefix + "Using Multiple Threads");
		else                 log.info(logPrefix + "Using Single Thread");

		// cron executor
		cronExecutorEnabled      = Config.getBoolean("CronExecutor.Enabled");
		long cronExecutorSeconds = 20 * Config.getLong("Tasks.CronExecutorSeconds");
		if (cronExecutorEnabled && cronExecutorSeconds>0) {
			waCronExecutorTask = new CronExecutorTask(this);
			waCronExecutorTask.setCronUrl(Config.getString("CronExecutor.Url"));
			// cron executor task (always multi-threaded)
			scheduler.scheduleAsyncRepeatingTask(this, waCronExecutorTask,
				(cronExecutorSeconds*2), cronExecutorSeconds);
			log.info(logPrefix + "Enabled Task: Cron Executor");
		}

		// announcer
		announceEnabled       = Config.getBoolean("Announcer.Enabled");
		long announcerSeconds = 20 * Config.getLong("Tasks.AnnouncerSeconds");
		if (announceEnabled && announcerSeconds>0) {
			waAnnouncerTask = new AnnouncerTask(this);
			waAnnouncerTask.chatPrefix     = Config.getString ("Announcer.Prefix");
			waAnnouncerTask.announceRandom = Config.getBoolean("Announcer.Random");
			waAnnouncerTask.addMessages(     Config.getStringList("Announcements"));
			if (UseMultithreads)
				scheduler.scheduleAsyncRepeatingTask(this, waAnnouncerTask,
					announcerSeconds-(announcerSeconds/2), announcerSeconds);
			else
				scheduler.scheduleSyncRepeatingTask (this, waAnnouncerTask,
					announcerSeconds-(announcerSeconds/2), announcerSeconds);
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

	}

	public void onSaveConfig() {
//		FileConfiguration Config = getConfig();
	}

	public void onDisable() {
		getServer().getScheduler().cancelTasks(this);
		log.info(logPrefix + "Disabled. Bye :D");
		dataQueries.forceCloseConnections();
	}

	public boolean ConnectDB() {
		// Init database
		log.info(logPrefix + "MySQL Initializing.");
		FileConfiguration Config = getConfig();
		try {
			dataQueries = new MySQLDataQueries(this,
				Config.getString("MySQL.Host"),
				Config.getString("MySQL.Port"),
				Config.getString("MySQL.Username"),
				Config.getString("MySQL.Password"),
				Config.getString("MySQL.Database"),
				Config.getString("MySQL.TablePrefix")
			);
			dataQueries.ConnPoolSizeWarn = Config.getInt("MySQL.ConnectionPoolSizeWarn");
			dataQueries.ConnPoolSizeHard = Config.getInt("MySQL.ConnectionPoolSizeHard");
			dataQueries.debugSQL         = Config.getBoolean("Development.DebugSQL");
		} catch (Exception e) {
			if (e.getCause() instanceof SQLException) {
				log.severe(logPrefix + "Unable to connect to MySQL database.");
			}
			e.printStackTrace();
			return false;
		}
		dataQueries.initTables();
		return true;
	}

	private void initConfig() {
		FileConfiguration Config = getConfig();
		Config.addDefault("MySQL.Host", "localhost");
		Config.addDefault("MySQL.Username", "minecraft");
		Config.addDefault("MySQL.Password", "password123");
		Config.addDefault("MySQL.Port", "3306");
		Config.addDefault("MySQL.Database", "minecraft");
		Config.addDefault("MySQL.TablePrefix", "WA_");
		Config.addDefault("MySQL.ConnectionPoolSizeWarn", 6);
		Config.addDefault("MySQL.ConnectionPoolSizeHard", 20);
		Config.addDefault("Misc.ReportSales", true);
		Config.addDefault("Misc.UseOriginalRecentSigns", true);
		Config.addDefault("Misc.ShowSalesOnJoin", true);
		Config.addDefault("Misc.SignClickDelay", 500);
		Config.addDefault("Misc.UnsafeEnchantments", false);
		Config.addDefault("Tasks.SaleAlertSeconds", 20L);
		Config.addDefault("Tasks.ShoutSignUpdateSeconds", 20L);
		Config.addDefault("Tasks.RecentSignUpdateSeconds", 60L);
		Config.addDefault("Tasks.CronExecutorSeconds", 3600L);
		Config.addDefault("Tasks.AnnouncerSeconds", 3600L);
		Config.addDefault("SignLink.Enabled", false);
		Config.addDefault("SignLink.NumberOfLatestAuctionsToTrack", 10);
		Config.addDefault("Development.UseMultithreads", false);
		Config.addDefault("Development.DebugSQL", false);
		Config.addDefault("CronExecutor.Enabled", false);
		Config.addDefault("CronExecutor.Url", "http://yourminecraftserver.com/webauction/cron.php");
		Config.addDefault("Announcer.Enabled", false);
		Config.addDefault("Announcer.Prefix", "&c[Auto] ");
		Config.addDefault("Announcer.Random", false);
		Config.addDefault("Announcements", new String[]{"This server is running WebAuctionPlus!"} );
		Config.options().copyDefaults(true);
		saveConfig();
	}

	private Boolean setupPermissions() {
		RegisteredServiceProvider<Permission> permissionProvider = getServer().getServicesManager().getRegistration(Permission.class);
		if (permissionProvider != null) {
			permission = (Permission)permissionProvider.getProvider();
		}
		return (permission != null);
	}

	private Boolean setupEconomy() {
		RegisteredServiceProvider<Economy> economyProvider = getServer().getServicesManager().getRegistration(Economy.class);
		if (economyProvider != null) {
			economy = (Economy)economyProvider.getProvider();
		}
		return (economy != null);
	}

	public String ReplaceColors(String text){
		return text.replaceAll("&([0-9a-fA-F])", "\247$1");
	}

	public int getNewRandom(int oldNumber, int maxNumber) {
		if (maxNumber == 0) return maxNumber;
		if (maxNumber == 1) return 1 - oldNumber;
		Random randomGen = new Random();
		int newNumber = 0;
		while (true) {
			newNumber = randomGen.nextInt(maxNumber + 1);
			if (newNumber != oldNumber) return newNumber;
		}
	}

	void PrintProgress(double progress, int width) {
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
		log.info(output + "]");
	}
	void PrintProgress(int count, int total, int width) {
		try {
			// finished 100%
			if (count == total) {
				PrintProgress( 1D, width);
			// total to few
			} else if (total < (width / 2) ) {
			// print only when adding a .
			} else if ( (int)(count % (total / width)) == 0) {
				PrintProgress( (double)count / (double)total, width);
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	void PrintProgress(int count, int total) {
		PrintProgress(count, total, 20);
	}

//	public double updateCheck(double currentVersion) throws Exception {
//	String pluginUrlString = "http://dev.bukkit.org/server-mods/webauctionplus/files.rss";
//	try {
//		URL url = new URL(pluginUrlString);
//		Document doc = DocumentBuilderFactory.newInstance().newDocumentBuilder().parse(url.openConnection().getInputStream());
//		doc.getDocumentElement().normalize();
//		NodeList nodes = doc.getElementsByTagName("item");
//		Node firstNode = nodes.item(0);
//		if (firstNode.getNodeType() == 1) {
//			Element firstElement = (Element)firstNode;
//			NodeList firstElementTagName = firstElement.getElementsByTagName("title");
//			Element firstNameElement = (Element) firstElementTagName.item(0);
//			NodeList firstNodes = firstNameElement.getChildNodes();
//log.info("=============" +  firstNodes.item(0).getNodeValue().replace("WebAuctionPlus", "").replaceFirst(".", "").trim() );
//			return Double.valueOf(firstNodes.item(0).getNodeValue().replace("WebAuctionPlus", "").replaceFirst(".", "").trim());
//		}
//	} catch (Exception e) {
//		e.printStackTrace();
//	}
//	return currentVersion;
//}

}