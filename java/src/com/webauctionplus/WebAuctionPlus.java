package com.webauctionplus;

import java.io.IOException;

import com.poixson.pxnCommon.JavaPlugin.pxnJavaPlugin;
import com.poixson.pxnCommon.Language.pxnLanguageMessages;
import com.poixson.pxnCommon.Logger.pxnLogger;
import com.poixson.pxnCommon.Metrics.pxnMetrics;
import com.poixson.pxnCommon.dbPool.dbPool;
import com.poixson.pxnCommon.dbPool.dbPoolConn;


public class WebAuctionPlus extends pxnJavaPlugin {

	// plugin info
	private static WebAuctionPlus plugin       = null;
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
	public static final pxnLogger log = new pxnLogger("WebAuction+");

	// database pool
	private static dbPool db = null;

	// stats
	private static pxnMetrics metrics = null;
@SuppressWarnings("unused")
	private static waStatsCache stats = null;

	// language
	private static pxnLanguageMessages language = null;




	public WebAuctionPlus() {
isDebug = true;
		// only one instance allowed
		if(plugin != null) {
			PluginAlreadyRunningMessage();
			return;
		}
		plugin = this;
		
	}








	// load plugin
	public void onEnable() {
log.info("starting..");
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
		// done
		log.info("WORKING!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
		isOk = true;
	}


	// unload plugin
	public void onDisable() {
	}



	// get WebAuctionPlus plugin instance
	public static WebAuctionPlus getPlugin() {
		return plugin;
	}


	// get logger
	@Override
	public pxnLogger getLog() {
		return log;
	}


	// get db lock from pool
	public static dbPoolConn getDB() {
		if(db == null)
			return null;
		return db.getLock();
	}


	// get language
	public static pxnLanguageMessages getLang() {
		synchronized(language) {
			if(language == null)
				language = pxnLanguageMessages.factory();
			return language;
		}
	}


	public void onLoadMetrics() {
		// usage stats
		try {
			metrics = new pxnMetrics(this);
			if(metrics.isOptOut()) {
				log.info("Plugin metrics are disabled, you bum.");
				return;
			}
			log.info("Starting metrics");
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
				log.exception(e);
			}
		}
	}








	@Override
	public String getPluginName() {
		// TODO Auto-generated method stub
		return null;
	}








	@Override
	public String getPluginFullName() {
		// TODO Auto-generated method stub
		return null;
	}






	@Override
	public String getRunningVersion() {
		// TODO Auto-generated method stub
		return null;
	}







}