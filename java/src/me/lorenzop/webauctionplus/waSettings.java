package me.lorenzop.webauctionplus;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class waSettings {

	WebAuctionPlus plugin;

	public waSettings(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public void LoadSettings(){
		Connection conn = plugin.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		int countSettings = 0;


// temp code until Settings class is finished
		try {
			st = conn.prepareStatement("SELECT COUNT(*) AS `count` FROM `"+plugin.dataQueries.dbPrefix+"Settings` WHERE `name`='Version'");
			rs = st.executeQuery();
			if (!rs.next()) {
				WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix + "Could not get settings!");
				return;
			}
			if (rs.getInt(1) == 0) {
				if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: Insert Version Setting");
				st = conn.prepareStatement("INSERT INTO `"+plugin.dataQueries.dbPrefix+"Settings` (`name`,`value`) VALUES ('Version',?)");
				st.setString(1, plugin.getDescription().getVersion().toString());
				st.executeUpdate();
			}
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update Players table!");
			e.printStackTrace();
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
		conn = plugin.dataQueries.getConnection();


		try {
			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: Settings::LoadSettings ");
			st = conn.prepareStatement("SELECT `name`,`value` FROM `"+plugin.dataQueries.dbPrefix+"Settings`");
			rs = st.executeQuery();
			while (rs.next()) {
				
				countSettings++;
			}
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Loaded " + Integer.toString(countSettings) + " Settings");
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to load settings!");
			e.printStackTrace();
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
	}

}
