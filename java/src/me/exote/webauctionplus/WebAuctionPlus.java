package me.exote.webauctionplus;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Random;
import java.util.logging.Logger;

import me.exote.webauctionplus.listeners.WebAuctionBlockListener;
import me.exote.webauctionplus.listeners.WebAuctionPlayerListener;
import me.exote.webauctionplus.listeners.WebAuctionServerListener;
import me.exote.webauctionplus.tasks.RecentSignTask;
import me.exote.webauctionplus.tasks.SaleAlertTask;
import me.exote.webauctionplus.tasks.ShoutSignTask;
import me.lorenzop.webauctionplus.tasks.AnnouncerTask;
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
	public String dbPrefix = "WA_";
	public WebAuctionCommands WebAuctionCommandsListener = new WebAuctionCommands(this);

	public Map<String,   Long>    lastSignUse = new HashMap<String , Long>();
	public Map<Location, Integer> recentSigns = new HashMap<Location, Integer>();
	public Map<Location, Integer> shoutSigns  = new HashMap<Location, Integer>();

	public int signDelay          = 0;
	public int numberOfRecentLink = 0;

	public Boolean useSignLink       = false;
	public Boolean useOriginalRecent = false;
	public Boolean showSalesOnJoin   = false;

	// announcer
	public boolean announceRandom = false;
	public List<String> announcementMessages;
	public String announcementPrefix = "";

	public Permission permission = null;
	public Economy    economy    = null;

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
		useSignLink        = Config.getBoolean("SignLink.UseSignLink");
		numberOfRecentLink = Config.getInt    ("SignLink.NumberOfLatestAuctionsToTrack");

		// connect MySQL
		ConnectDB();

		// Build shoutSigns map
		shoutSigns.putAll(dataQueries.getShoutSignLocations());
		// Build recentSigns map
		recentSigns.putAll(dataQueries.getRecentSignLocations());

		// announcer
		announcementPrefix   = Config.getString("Announcer.Prefix");
		announceRandom       = Config.getBoolean("Announcer.Random");
		announcementMessages = Config.getStringList("Announcements");

		// enable scheduled tasks
		BukkitScheduler scheduler = getServer().getScheduler();
		long saleAlertFrequency        = 20 * Config.getLong("Tasks.SaleAlertSeconds");
		long shoutSignUpdateFrequency  = 20 * Config.getLong("Tasks.ShoutSignUpdateSeconds");
		long recentSignUpdateFrequency = 20 * Config.getLong("Tasks.RecentSignUpdateSeconds");
		long announcerFrequency        = 20 * Config.getLong("Tasks.AnnouncerSeconds");
		useOriginalRecent              = Config.getBoolean("Misc.UseOriginalRecentSigns");
		// multi-threaded
		if (Config.getBoolean("Development.UseMultithreads")){
			log.info(logPrefix + "Using Multiple Threads");
			// report sales to players
			if (saleAlertFrequency > 0)
				scheduler.scheduleAsyncRepeatingTask(this, new SaleAlertTask(this),
					saleAlertFrequency, saleAlertFrequency);
			log.info(logPrefix + (saleAlertFrequency>0?"Enabled":"Disabled") + " Sale Alert Task");
			// shout sign task
			if (shoutSignUpdateFrequency > 0)
				scheduler.scheduleAsyncRepeatingTask(this, new ShoutSignTask(this),
					shoutSignUpdateFrequency+(shoutSignUpdateFrequency/2), shoutSignUpdateFrequency);
			log.info(logPrefix + (shoutSignUpdateFrequency>0?"Enabled":"Disabled") + " Shout Sign Task");
			// update recent signs
			if (recentSignUpdateFrequency > 0)
				scheduler.scheduleAsyncRepeatingTask(this, new RecentSignTask(this),
					recentSignUpdateFrequency-(recentSignUpdateFrequency/2), recentSignUpdateFrequency);
			log.info(logPrefix + (recentSignUpdateFrequency>0?"Enabled":"Disabled") + " Recent Sign Task");
			// announcer task
			if (announcerFrequency > 0)
				scheduler.scheduleAsyncRepeatingTask(this, new AnnouncerTask(this),
						announcerFrequency-(announcerFrequency/2), announcerFrequency);
			log.info(logPrefix + (announcerFrequency>0?"Enabled":"Disabled") + " Announcer Task");
		// single-threaded
		}else{
			log.info(logPrefix + "Using Single Thread");
			// shout sign task
			if (saleAlertFrequency > 0)
				scheduler.scheduleSyncRepeatingTask(this, new SaleAlertTask(this),
					saleAlertFrequency, saleAlertFrequency);
			log.info(logPrefix + (saleAlertFrequency>0?"Enabled":"Disabled") + " Sale Alert Task");
			// shout sign task
			if (shoutSignUpdateFrequency > 0)
				scheduler.scheduleSyncRepeatingTask(this, new ShoutSignTask(this),
					shoutSignUpdateFrequency+(shoutSignUpdateFrequency/2), shoutSignUpdateFrequency);
			log.info(logPrefix + (shoutSignUpdateFrequency>0?"Enabled":"Disabled") + " Shout Sign Task");
			// update recent signs
			if (recentSignUpdateFrequency > 0)
				scheduler.scheduleSyncRepeatingTask(this, new RecentSignTask(this),
					recentSignUpdateFrequency-(recentSignUpdateFrequency/2), recentSignUpdateFrequency);
			log.info(logPrefix + (recentSignUpdateFrequency>0?"Enabled":"Disabled") + " Recent Sign Task");
			// announcer task
			if (announcerFrequency > 0)
				scheduler.scheduleSyncRepeatingTask(this, new AnnouncerTask(this),
						announcerFrequency-(announcerFrequency/2), announcerFrequency);
			log.info(logPrefix + (announcerFrequency>0?"Enabled":"Disabled") + " Announcer Task");
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
				Config.getString("MySQL.Database") );
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
		dbPrefix = Config.getString("MySQL.TablePrefix");
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
		Config.addDefault("Tasks.SaleAlertSeconds", 20L);
		Config.addDefault("Tasks.ShoutSignUpdateSeconds", 20L);
		Config.addDefault("Tasks.RecentSignUpdateSeconds", 60L);
		Config.addDefault("Tasks.AnnouncerSeconds", 3600L);
		Config.addDefault("SignLink.UseSignLink", false);
		Config.addDefault("SignLink.NumberOfLatestAuctionsToTrack", 10);
		Config.addDefault("Development.UseMultithreads", false);
		Config.addDefault("Development.DebugSQL", false);
		Config.addDefault("Announcer.Prefix", "&c[Auto] ");
		Config.addDefault("Announcer.Random", true);
		Config.addDefault("Announcements", new String[]{"This is a default announcement."} );
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