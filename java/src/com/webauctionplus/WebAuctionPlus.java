package com.webauctionplus;

import java.io.IOException;

import com.poixson.pxnCommon.JavaPlugin.pxnConfig;
import com.poixson.pxnCommon.JavaPlugin.pxnJavaPlugin;
import com.poixson.pxnCommon.Logger.pxnLogger;
import com.poixson.pxnCommon.Metrics.pxnMetrics;
import com.poixson.pxnCommon.dbPool.dbPool;


public class WebAuctionPlus extends pxnJavaPlugin {

	// plugin info
	protected static WebAuctionPlus waPlugin     = null;
@SuppressWarnings("unused")
	private static final String pluginName     = "";

	// version
@SuppressWarnings("unused")
	private static final String pluginVersion  = "";
@SuppressWarnings("unused")
	private static String versionAvailable     = "";
@SuppressWarnings("unused")
	private static boolean newVersionAvailable = false;



	// log
//TODO: maybe make a separate chat formatter
//	public static final FormatListener chatPrefix = FormatText.setChatPrefix("{darkgreen}[{white}WebAuction{darkgreen}] ");
//	public static final pxnLogger log = new pxnLogger("WebAuction+");

	// stats
	private static pxnMetrics metrics = null;
@SuppressWarnings("unused")
	private static waStatsCache stats = null;


	public WebAuctionPlus() {
		super();
isDebug = true;
		log = new pxnLogger(getPluginName());
		// only one instance allowed
		if(waPlugin != null) return;
		if(!isOk()) return;
		waPlugin = this;
	}


	// load plugin
	public void onEnable() {
		if(!isOk()) return;
getLog().info("starting..");
		// init metrics
		try {
			metrics = new pxnMetrics(this, "testPlugin");
		} catch (IOException e) {
			e.printStackTrace();
		}
		// register listeners
//		PluginManager pm = getServer().getPluginManager();
//		pm.registerEvents(new WebAuctionPlayerListener(this), this);
//		pm.registerEvents(new WebAuctionBlockListener (this), this);


		// load config.yml
		_LoadConfig();



		// connect to database
		db = new dbPool("127.0.0.1", 3306, "testuser", "testpass", "bukkitT");



		// done
		getLog().info("WORKING!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
		isOk = true;
	}


	// unload plugin
	public void onDisable() {
	}




	private void _LoadConfig() {

		config = new pxnConfig(this);




//		// load settings from db
//		if(settings != null) settings = null;
//		settings = new waSettings(this);
//		settings.LoadSettings();
//		if(!settings.isOk()) {onDisable(); return false;}
//
//		// update the version in db
//		if(! currentVersion.equals(settings.getString("Version")) ){
//			String oldVersion = settings.getString("Version");
//			// update database
//			MySQLUpdate.doUpdate(oldVersion);
//			// update version number
//			settings.setString("Version", currentVersion);
//			log.info(logPrefix+"Updated version from "+oldVersion+" to "+currentVersion);
//		}
//
//		// load language file
//		if(Lang != null) Lang = null;
//		Lang = new Language(this);
//		Lang.loadLanguage(settings.getString("Language"));
//		if(!Lang.isOk()) {onDisable(); return false;}
//
//		try {
//			isDebug = config.getBoolean("Development.Debug");
//			addComment("debug_mode", Arrays.asList("# This is where you enable debug mode"))
//			signDelay          = config.getInt    ("Misc.SignClickDelay");
//			timEnabled         = config.getBoolean("Misc.UnsafeEnchantments");
//			announceGlobal     = config.getBoolean("Misc.AnnounceGlobally");
//			numberOfRecentLink = config.getInt    ("SignLink.NumberOfLatestAuctionsToTrack");
//			useSignLink        = config.getBoolean("SignLink.Enabled");
//			if(useSignLink)
//				if(!Bukkit.getPluginManager().getPlugin("SignLink").isEnabled()) {
//					log.warning(logPrefix+"SignLink is enabled but plugin is not loaded!");
//					useSignLink = false;
//				}
//
//			// scheduled tasks
//			BukkitScheduler scheduler = Bukkit.getScheduler();
//			boolean UseMultithreads = config.getBoolean("Development.UseMultithreads");
//
//			// announcer
//			announcerEnabled = config.getBoolean("Announcer.Enabled");
//			long announcerMinutes = 20 * 60 * config.getLong("Tasks.AnnouncerMinutes");
//			if(announcerEnabled) waAnnouncerTask = new AnnouncerTask(this);
//			if (announcerEnabled && announcerMinutes>0) {
//				if(announcerMinutes < 6000) announcerMinutes = 6000; // minimum 5 minutes
//				waAnnouncerTask.chatPrefix     = config.getString ("Announcer.Prefix");
//				waAnnouncerTask.announceRandom = config.getBoolean("Announcer.Random");
//				waAnnouncerTask.addMessages(     config.getStringList("Announcements"));
//				scheduler.scheduleAsyncRepeatingTask(this, waAnnouncerTask,
//					(announcerMinutes/2), announcerMinutes);
//				log.info(logPrefix + "Enabled Task: Announcer (always multi-threaded)");
//			}
//
//			long saleAlertSeconds        = 20 * config.getLong("Tasks.SaleAlertSeconds");
//			long shoutSignUpdateSeconds  = 20 * config.getLong("Tasks.ShoutSignUpdateSeconds");
//			long recentSignUpdateSeconds = 20 * config.getLong("Tasks.RecentSignUpdateSeconds");
//			useOriginalRecent            =      config.getBoolean("Misc.UseOriginalRecentSigns");
//
//			// Build shoutSigns map
//			if (shoutSignUpdateSeconds > 0)
//				shoutSigns.putAll(DataQueries.getShoutSignLocations());
//			// Build recentSigns map
//			if (recentSignUpdateSeconds > 0)
//				recentSigns.putAll(DataQueries.getRecentSignLocations());
//
//			// report sales to players (always multi-threaded)
//			if (saleAlertSeconds > 0) {
//				if(saleAlertSeconds < 3*20) saleAlertSeconds = 3*20;
//				scheduler.scheduleAsyncRepeatingTask(this, new PlayerAlertTask(),
//					saleAlertSeconds, saleAlertSeconds);
//				log.info(logPrefix + "Enabled Task: Sale Alert (always multi-threaded)");
//			}
//			// shout sign task
//			if (shoutSignUpdateSeconds > 0) {
//				if (UseMultithreads)
//					scheduler.scheduleAsyncRepeatingTask(this, new ShoutSignTask(this),
//						shoutSignUpdateSeconds, shoutSignUpdateSeconds);
//				else
//					scheduler.scheduleSyncRepeatingTask (this, new ShoutSignTask(this),
//						shoutSignUpdateSeconds, shoutSignUpdateSeconds);
//				log.info(logPrefix + "Enabled Task: Shout Sign (using " + (UseMultithreads?"multiple threads":"single thread") + ")");
//			}
//			// update recent signs
//			if(recentSignUpdateSeconds > 0 && useOriginalRecent) {
//				recentSignTask = new RecentSignTask(this);
//				if (UseMultithreads)
//					scheduler.scheduleAsyncRepeatingTask(this, recentSignTask,
//						5*20, recentSignUpdateSeconds);
//				else
//					scheduler.scheduleSyncRepeatingTask (this, recentSignTask,
//						5*20, recentSignUpdateSeconds);
//				log.info(logPrefix + "Enabled Task: Recent Sign (using " + (UseMultithreads?"multiple threads":"single thread") + ")");
//			}
//		} catch (Exception e) {
//			log.severe("Unable to load config");
//			e.printStackTrace();
//			return false;
//		}
//		return true;



	}








	public static WebAuctionPlus getPlugin() {
		return waPlugin;
	}
//	public static dbPoolConn getDBLock() {
//		return getPlugin().getDB();
//	}


	public void onLoadMetrics() {
		// usage stats
		try {
			metrics = new pxnMetrics(this);
			if(metrics.isOptOut()) {
				getLog().info("Plugin metrics are disabled, you bum.");
				return;
			}
			getLog().info("Starting metrics");
			// Create graphs for total Buy Nows / Auctions
			pxnMetrics.Graph lineGraph = metrics.createGraph("Stacks For Sale");
			pxnMetrics.Graph pieGraph  = metrics.createGraph("Selling Method");
			// buy now count
			pxnMetrics.Plotter plotterBuyNows = new pxnMetrics.Plotter("Buy Nows") {
				@Override
				public int getValue(){
//					return stats.getTotalBuyNows();
return 0;
				}
			};
			// auction count
			pxnMetrics.Plotter plotterAuctions = new pxnMetrics.Plotter("Auctions") {
				@Override
				public int getValue(){
//					return stats.getTotalAuctions();
return 0;
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
			if(isDebug()) {
				getLog().exception(e);
			}
		}
	}


	@Override
	public String getPluginName() {
		return "WebAuctionPlus3.0";
	}
//	@Override
//	public String getPluginFullName() {
//return null;
//	}
//	@Override
//	public String getRunningVersion() {
//return null;
//	}


}