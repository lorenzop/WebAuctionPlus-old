package me.lorenzop.webauctionplus.dao;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import me.lorenzop.webauctionplus.WebAuctionPlus;

public class waStats {

	// long cycle stats
	private int  totalBuyNowCount	= 0;
	private int  totalAuctionCount	= 0;

	// short cycle stats
	private int  maxAuctionId		=-1;

	private long lastUpdateLong		=-1;
	private long lastUpdateShort	=-1;

	public waStats() {
	}

	private synchronized boolean Update(boolean updateAll) {
		long tim = WebAuctionPlus.getCurrentMilli();
		boolean didUpdate = false;
		// update long cycle (every 5 minutes min)
		if(updateAll || lastUpdateLong == -1) {
			if( ((tim-lastUpdateLong) >= (300*1000)) || lastUpdateLong == -1) {
				updateLong();
				lastUpdateLong = tim;
				didUpdate = true;
			}
		}
		// update short cycle (every 10 seconds min)
		if( (tim-lastUpdateShort) >= (10*1000) || lastUpdateShort == -1) {
			updateShort();
			lastUpdateShort = tim;
			didUpdate = true;
		}
		return didUpdate;
	}


	// update long cycle (5 minutes)
	private boolean updateLong() {
		if (WebAuctionPlus.dataQueries.debugSQL()) WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Updating stats");
		Connection conn;
		PreparedStatement st;
		ResultSet rs;
		// total buy nows
		totalBuyNowCount = 0;
		conn = WebAuctionPlus.dataQueries.getConnection();
		st = null;
		rs = null;
		try {
			if (WebAuctionPlus.dataQueries.debugSQL()) WebAuctionPlus.log.info("WA Query: Stats::count buy nows");
			st = conn.prepareStatement("SELECT COUNT(*) FROM `"+WebAuctionPlus.dataQueries.dbPrefix()+"Auctions` WHERE `allowBids` = 0");
			rs = st.executeQuery();
			if (rs.next())
				totalBuyNowCount = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get total buy now count");
			e.printStackTrace();
		} finally {
			WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
		}
		// total auctions
		totalAuctionCount = 0;
		conn = WebAuctionPlus.dataQueries.getConnection();
		st = null;
		rs = null;
		try {
			if (WebAuctionPlus.dataQueries.debugSQL()) WebAuctionPlus.log.info("WA Query: Stats::count auctions");
			st = conn.prepareStatement("SELECT COUNT(*) FROM `"+WebAuctionPlus.dataQueries.dbPrefix()+"Auctions` WHERE `allowBids` != 0");
			rs = st.executeQuery();
			if (rs.next())
				totalAuctionCount = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get total auction count");
			e.printStackTrace();
		} finally {
			WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
		}
		// finished updating stats
		return true;
	}

	// update short cycle (10 seconds)
	private void updateShort() {
		// get max auction id
		maxAuctionId = -1;
		Connection conn = WebAuctionPlus.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (WebAuctionPlus.dataQueries.debugSQL()) WebAuctionPlus.log.info("WA Query: getMaxAuctionID");
			st = conn.prepareStatement("SELECT MAX(`id`) FROM `"+WebAuctionPlus.dataQueries.dbPrefix()+"Auctions`");
			rs = st.executeQuery();
			if (rs.next())
				maxAuctionId = rs.getInt(1);
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to query for max Auction ID");
			e.printStackTrace();
		} finally {
			WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
		}
	}


	// data access layer
	// long cycle
	public int getTotalBuyNows() {
		Update(true);
		return totalBuyNowCount;
	}
	public int getTotalAuctions() {
		Update(true);
		return totalAuctionCount;
	}
	// short cycle
	public int getMaxAuctionID() {
		Update(false);
		return maxAuctionId;
	}


}
