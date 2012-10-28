package me.lorenzop.webauctionplus.dao;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.mysql.MySQLPoolConn;

public class waStats {

	// long cycle stats
	private int  totalBuyNowCount	= 0;
	private int  totalAuctionCount	= 0;
	private int  maxAuctionId		=-1;

	private long lastUpdate =-1;


	public waStats() {
	}

	private synchronized boolean Update() {
		long tim = WebAuctionPlus.getCurrentMilli();
		// update no more than every 5 seconds
		if( lastUpdate == -1 || ((tim-lastUpdate) >= (5000)) ) {
			doUpdate();
			lastUpdate = tim;
			return true;
		}
		return false;
	}


	private void doUpdate() {
		if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Updating stats");
		MySQLPoolConn poolConn = WebAuctionPlus.dbPool.getLock();
		PreparedStatement st = null;
		ResultSet rs = null;

		// total buy nows
		totalBuyNowCount = 0;
		try {
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: Stats::count buy nows");
			st = poolConn.getConn().prepareStatement("SELECT COUNT(*) FROM `"+poolConn.dbPrefix()+"Auctions` WHERE `allowBids` = 0");
			rs = st.executeQuery();
			if(rs.next()) totalBuyNowCount = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get total buy now count");
			e.printStackTrace();
		} finally {
			poolConn.freeResource(st, rs);
		}

		// total auctions
		totalAuctionCount = 0;
		try {
			st = null;
			rs = null;
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: Stats::count auctions");
			st = poolConn.getConn().prepareStatement("SELECT COUNT(*) FROM `"+poolConn.dbPrefix()+"Auctions` WHERE `allowBids` != 0");
			rs = st.executeQuery();
			if(rs.next()) totalAuctionCount = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get total auction count");
			e.printStackTrace();
		} finally {
			poolConn.freeResource(st, rs);
		}

		// get max auction id
		maxAuctionId = -1;
		try {
			st = null;
			rs = null;
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: Stats::getMaxAuctionID");
			st = poolConn.getConn().prepareStatement("SELECT MAX(`id`) AS `id` FROM `"+poolConn.dbPrefix()+"Auctions`");
			rs = st.executeQuery();
			if(rs.next()) maxAuctionId = rs.getInt("id");
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to query for max Auction ID");
			e.printStackTrace();
		} finally {
			poolConn.freeResource(st, rs);
		}

		poolConn.releaseLock();
		poolConn = null;
	}


	// data access layer
	public int getTotalBuyNows() {
		Update();
		return totalBuyNowCount;
	}
	public int getTotalAuctions() {
		Update();
		return totalAuctionCount;
	}
	public int getMaxAuctionID() {
		Update();
		return maxAuctionId;
	}


}
