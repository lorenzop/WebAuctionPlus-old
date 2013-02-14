package com.webauctionplus;

import java.io.IOException;

import com.poixson.pxnCommon.BukkitPlugin.pxnPlugin;
import com.poixson.pxnCommon.Logger.FormatChat;
import com.poixson.pxnCommon.Metrics.pxnMetrics;
import com.poixson.pxnCommon.Task.pxnTask;
import com.webauctionplus.listeners.waListenerCommand;
import com.webauctionplus.tasks.TaskAnnouncer;
import com.webauctionplus.tasks.TaskUpdatePlayers;


public class WebAuctionPlus extends pxnPlugin {

	// plugin info
	private static WebAuctionPlus plugin = null;
	protected FormatChat chat = new FormatChat("{darkgreen}[{white}WebAuction{darkgreen}] ");

	// listeners
	private waListenerCommand listenerCommand = null;

	// stats
	private static pxnMetrics metrics = null;
	private static waStatsCache statsCache = null;

	// tasks
	private pxnTask taskUpdatePlayers = null;
	private pxnTask taskAnnouncer     = null;


	public WebAuctionPlus() {
		super();
		// only one instance allowed
		plugin = (WebAuctionPlus) SingleInstance(plugin, this);
isDebug = true;
	}
	// plugin name
	@Override
	public String getPluginName() {
		return "WebAuctionPlus";
	}
	// get plugin instance
	public static WebAuctionPlus getPlugin() {
		return plugin;
	}


	// load plugin
	protected void StartPlugin() {
		// already loaded
		if(okEquals(true))
			StopPlugin();
		// starting plugin
		getLog().info("Starting..");

		// command listener
		if(listenerCommand == null) {
			listenerCommand = new waListenerCommand();
			registerListener(listenerCommand);
		}

		// load config
		LoadConfig();

//TODO:
//		// connect to database
//db = new dbPool(this, "127.0.0.1", 3306, "testuser", "testpass", "bukkitT");

		// load stats cache
		if(statsCache == null)
			statsCache = new waStatsCache();

//TODO:
//		// load settings db
//		if(settings != null) settings = null;
//		settings = new waSettings(this);
//		settings.LoadSettings();
//		if(!settings.isOk()) {onDisable(); return false;}
		
//TODO:
//		// update the version in db
//		if(! currentVersion.equals(settings.getString("Version")) ){
//			String oldVersion = settings.getString("Version");
//			// update database
//			MySQLUpdate.doUpdate(oldVersion);
//			// update version number
//			settings.setString("Version", currentVersion);
//			log.info(logPrefix+"Updated version from "+oldVersion+" to "+currentVersion);
//		}

//TODO:
//		// load language file
//		if(Lang != null) Lang = null;
//		Lang = new Language(this);
//		Lang.loadLanguage(settings.getString("Language"));
//		if(!Lang.isOk()) {onDisable(); return false;}

//TODO:
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

//TODO:
//			// scheduled tasks
//			BukkitScheduler scheduler = Bukkit.getScheduler();
//			boolean UseMultithreads = config.getBoolean("Development.UseMultithreads");

//TODO:
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

//TODO:
//			long saleAlertSeconds        = 20 * config.getLong("Tasks.SaleAlertSeconds");
//			long shoutSignUpdateSeconds  = 20 * config.getLong("Tasks.ShoutSignUpdateSeconds");
//			long recentSignUpdateSeconds = 20 * config.getLong("Tasks.RecentSignUpdateSeconds");
//			useOriginalRecent            =      config.getBoolean("Misc.UseOriginalRecentSigns");

//TODO:
//			// Build shoutSigns map
//			if (shoutSignUpdateSeconds > 0)
//				shoutSigns.putAll(dataQueries.getShoutSignLocations());
//			// Build recentSigns map
//			if (recentSignUpdateSeconds > 0)
//				recentSigns.putAll(dataQueries.getRecentSignLocations());

//TODO:
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

//TODO:
//		//### register listeners
//		try {
//			PluginManager pm = getServer().getPluginManager();
//this is in pxn			// server listener
//			}
//		} catch (Exception e) {
//			e.printStackTrace();
//		}
//		if( == null)
//		pm.registerEvents(new WebAuctionPlayerListener(this), this);
//		if( == null)
//		pm.registerEvents(new WebAuctionBlockListener (this), this);

		// update players task
		taskUpdatePlayers = new TaskUpdatePlayers()
			.setDelay(1)
			.setPeriod(20)
			.Start();
		// announcer task
		taskAnnouncer = new TaskAnnouncer()
			.setDelay(1)
			.setPeriod(20)
			.Start();

		// init metrics
		try {
			metrics = new pxnMetrics(this, "TestPlugin2");
		} catch (IOException e) {
			e.printStackTrace();
		}

//TODO:
//		CheckUpdateAvailable();

		// done
getLog().info("WORKING!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
		setOk(true);
	}


	// unload plugin
	protected void StopPlugin() {
		// stop tasks
		try {
			if(taskUpdatePlayers != null)
				taskUpdatePlayers.Stop();
			taskUpdatePlayers = null;
		} catch (Exception ignore) {}
		try {
			if(taskAnnouncer != null)
				taskAnnouncer.Stop();
			taskAnnouncer = null;
		} catch (Exception ignore) {}
		// reset plugin state
		setOk(false);
		errorMsgs.clear();
	}


	private void LoadConfig() {
//		config = new pxnConfig(this);

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
return 11;
				}
			};
			// auction count
			pxnMetrics.Plotter plotterAuctions = new pxnMetrics.Plotter("Auctions") {
				@Override
				public int getValue(){
//					return stats.getTotalAuctions();
return 11;
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


}