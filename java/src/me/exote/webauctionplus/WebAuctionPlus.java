package me.exote.webauctionplus;

import java.util.HashMap;
import java.util.Map;
import java.util.logging.Logger;

import me.exote.webauctionplus.listeners.WebAuctionBlockListener;
import me.exote.webauctionplus.listeners.WebAuctionPlayerListener;
import me.exote.webauctionplus.listeners.WebAuctionServerListener;
import me.exote.webauctionplus.tasks.RecentSignTask;
import me.exote.webauctionplus.tasks.SaleAlertTask;
import me.exote.webauctionplus.tasks.ShoutSignTask;
import net.milkbowl.vault.economy.Economy;
import net.milkbowl.vault.permission.Permission;

import org.bukkit.ChatColor;
import org.bukkit.Location;
import org.bukkit.plugin.PluginManager;
import org.bukkit.plugin.RegisteredServiceProvider;
import org.bukkit.plugin.java.JavaPlugin;

public class WebAuctionPlus extends JavaPlugin {

	public String logPrefix = "[WebAuction+] ";
	public String chatPrefix = ChatColor.DARK_GREEN + "[" + ChatColor.WHITE + "WebAuction+" + ChatColor.DARK_GREEN + "] ";
	public Logger log = Logger.getLogger("Minecraft");

	public Double currentVersion;
	public Double newVersion;

	public MySQLDataQueries dataQueries;
	public String dbPrefix = "WA_";
	public WebAuctionCommands WebAuctionCommandsListener = new WebAuctionCommands(this);

	public Map<String, Long> lastSignUse = new HashMap<String , Long>();
	public Map<Location, Integer> recentSigns = new HashMap<Location, Integer>();
	public Map<Location, Integer> shoutSigns = new HashMap<Location, Integer>();

	public int signDelay = 0;
	public int numberOfRecentLink = 0;

	public Boolean useSignLink = false;
	public Boolean useOriginalRecent = true;

	public Boolean showSalesOnJoin = false;

	public Permission permission = null;
	public Economy economy = null;

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

		// Init configs
		initConfig();
		String dbHost = getConfig().getString("MySQL.Host");
		String dbUser = getConfig().getString("MySQL.Username");
		String dbPass = getConfig().getString("MySQL.Password");
		String dbPort = getConfig().getString("MySQL.Port");
		String dbDatabase = getConfig().getString("MySQL.Database");
		dbPrefix = getConfig().getString("MySQL.TablePrefix");
		long saleAlertFrequency = getConfig().getLong("Updates.SaleAlertFrequency");
		long shoutSignUpdateFrequency = getConfig().getLong("Updates.ShoutSignUpdateFrequency");
		long recentSignUpdateFrequency = getConfig().getLong("Updates.RecentSignUpdateFrequency");
		boolean getMessages = getConfig().getBoolean("Misc.ReportSales");
		useOriginalRecent = getConfig().getBoolean("Misc.UseOriginalRecentSigns");
		showSalesOnJoin = getConfig().getBoolean("Misc.ShowSalesOnJoin");
		boolean useMultithreads = getConfig().getBoolean("Development.UseMultithreads");
		signDelay = getConfig().getInt("Misc.SignDelay");
		useSignLink = getConfig().getBoolean("SignLink.UseSignLink");
		numberOfRecentLink = getConfig().getInt("SignLink.NumberOfLatestAuctionsToTrack");

		// Command listener
		getCommand("wa").setExecutor(WebAuctionCommandsListener);

		setupEconomy();
		setupPermissions();

		// Init database
		dataQueries = new MySQLDataQueries(this, dbHost, dbPort, dbUser, dbPass, dbDatabase);
		log.info(logPrefix + "MySQL Initializing.");
		dataQueries.initTables();

		// Build shoutSigns map
		shoutSigns.putAll(dataQueries.getShoutSignLocations());
		// Build recentSigns map
		recentSigns.putAll(dataQueries.getRecentSignLocations());

		// If reporting sales in game, schedule sales alert task
		if (useMultithreads){
			log.info(logPrefix + "Using Multiple Threads.");
			if (getMessages) {
				getServer().getScheduler().scheduleAsyncRepeatingTask(this, new SaleAlertTask(this), saleAlertFrequency, saleAlertFrequency);
			}

			getServer().getScheduler().scheduleAsyncRepeatingTask(this, new ShoutSignTask(this), shoutSignUpdateFrequency, shoutSignUpdateFrequency);
			getServer().getScheduler().scheduleAsyncRepeatingTask(this, new RecentSignTask(this), recentSignUpdateFrequency, recentSignUpdateFrequency);
		}else{
			log.info(logPrefix + "Using Single Thread.");
			if (getMessages) {
				getServer().getScheduler().scheduleSyncRepeatingTask(this, new SaleAlertTask(this), saleAlertFrequency, saleAlertFrequency);
			}

			getServer().getScheduler().scheduleSyncRepeatingTask(this, new ShoutSignTask(this), shoutSignUpdateFrequency, shoutSignUpdateFrequency);
			getServer().getScheduler().scheduleSyncRepeatingTask(this, new RecentSignTask(this), recentSignUpdateFrequency, recentSignUpdateFrequency);
		}

		PluginManager pm = getServer().getPluginManager();
		pm.registerEvents(new WebAuctionPlayerListener(this), this);
		pm.registerEvents(new WebAuctionBlockListener (this), this);
		pm.registerEvents(new WebAuctionServerListener(this), this);
	}

	public void onDisable() {
		getServer().getScheduler().cancelTasks(this);
		log.info(logPrefix + "Disabled. Bye :D");
	}

	private void initConfig() {
		getConfig().addDefault("MySQL.Host", "localhost");
		getConfig().addDefault("MySQL.Username", "minecraft");
		getConfig().addDefault("MySQL.Password", "password123");
		getConfig().addDefault("MySQL.Port", "3306");
		getConfig().addDefault("MySQL.Database", "minecraft");
		getConfig().addDefault("MySQL.TablePrefix", "WA_");
		getConfig().addDefault("Misc.ReportSales", true);
		getConfig().addDefault("Misc.UseOriginalRecentSigns", true);
		getConfig().addDefault("Misc.ShowSalesOnJoin", true);
		getConfig().addDefault("Development.UseMultithreads", false);
		getConfig().addDefault("Misc.SignDelay", 1000);
		getConfig().addDefault("SignLink.UseSignLink", false);
		getConfig().addDefault("SignLink.NumberOfLatestAuctionsToTrack", 10);
		getConfig().addDefault("Updates.SaleAlertFrequency", 100L);
		getConfig().addDefault("Updates.ShoutSignUpdateFrequency", 100L);
		getConfig().addDefault("Updates.RecentSignUpdateFrequency", 200L);
		getConfig().options().copyDefaults(true);
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