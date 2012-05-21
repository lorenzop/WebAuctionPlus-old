package me.lorenzop.webauctionplus.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import me.lorenzop.webauctionplus.WebAuctionPlus;

public class waStats {

	private WebAuctionPlus plugin;

	private long lastUpdate = -1;
	public int totalBuyNowCount = 0;
	public int totalAuctionCount = 0;

	private long lastUpdateMaxAId = -1;
	public int maxAuctionId = -1;

	public waStats(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public synchronized boolean Update() {
		long tim = plugin.getCurrentMilli();
		// update no more than every 5 minutes
		if(lastUpdate!=-1)
			if(tim-lastUpdate<(300*1000))
				return false;
		lastUpdate = tim;
		if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Updating stats");
		Connection conn;
		PreparedStatement st;
		ResultSet rs;
		// total buy nows
		totalBuyNowCount = 0;
		conn = plugin.dataQueries.getConnection();
		st = null;
		rs = null;
		try {
			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: Stats::count buy nows");
			st = conn.prepareStatement("SELECT COUNT(*) FROM `"+plugin.dataQueries.dbPrefix+"Auctions` WHERE `allowBids` = 0");
			rs = st.executeQuery();
			if (rs.next())
				totalBuyNowCount = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get total buy now count");
			e.printStackTrace();
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
		// total auctions
		totalAuctionCount = 0;
		conn = plugin.dataQueries.getConnection();
		st = null;
		rs = null;
		try {
			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: Stats::count auctions");
			st = conn.prepareStatement("SELECT COUNT(*) FROM `"+plugin.dataQueries.dbPrefix+"Auctions` WHERE `allowBids` != 0");
			rs = st.executeQuery();
			if (rs.next())
				totalAuctionCount = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get total auction count");
			e.printStackTrace();
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
		// finished updating stats
		return true;
	}

	public synchronized int getMaxAuctionID() {
		long tim = plugin.getCurrentMilli();
		// update no more than every 10 seconds
		if(lastUpdateMaxAId!=-1)
			if(tim-lastUpdateMaxAId<(10*1000))
				return maxAuctionId;
		lastUpdateMaxAId = tim;
		// get max auction id
		maxAuctionId = -1;
		Connection conn = plugin.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: getMaxAuctionID");
			st = conn.prepareStatement("SELECT MAX(`id`) FROM `"+plugin.dataQueries.dbPrefix+"Auctions`");
			rs = st.executeQuery();
			if (rs.next())
				maxAuctionId = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to query for max Auction ID");
			e.printStackTrace();
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
		return maxAuctionId;
	}

}
