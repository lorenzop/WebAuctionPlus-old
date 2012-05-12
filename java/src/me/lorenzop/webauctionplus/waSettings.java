package me.lorenzop.webauctionplus;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;

public class waSettings {

	WebAuctionPlus plugin;

	protected HashMap<String, String> settingsMap = new HashMap<String, String>();

	public waSettings(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public void LoadSettings(){
		Connection conn = plugin.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: LoadSettings");
			st = conn.prepareStatement("SELECT `name`, `value` FROM `"+plugin.dataQueries.dbPrefix+"Settings`");
			rs = st.executeQuery();
			while (rs.next()) {
				if(rs.getString(1) != null)
					settingsMap.put(rs.getString(1), rs.getString(2));
			}
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get settings");
			e.printStackTrace();
			return;
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
		WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Loaded " + Integer.toString(settingsMap.size()) + " settings from db");
	}

	public void addDefault(String name, String value) {
		if(!settingsMap.containsKey(name)) {
//			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: Insert setting: " + name);
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Adding default setting for " + name);
			Connection conn = plugin.dataQueries.getConnection();
			PreparedStatement st = null;
			ResultSet rs = null;
			try {
				st = conn.prepareStatement("INSERT INTO `"+plugin.dataQueries.dbPrefix+"Settings` (`name`,`value`) VALUES (?, ?)");
				st.setString(1, name);
				st.setString(2, value);
				st.executeUpdate();
			} catch (SQLException e) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to add setting " + name);
				e.printStackTrace();
			} finally {
				plugin.dataQueries.closeResources(conn, st, rs);
			}
		}
	}

	// get setting
	public String getString(String name) {
		if(settingsMap.containsKey(name))
			return settingsMap.get(name);
		else
			return null;
	}
	public int getInteger(String name) {
		return Integer.valueOf(this.getString(name));
	}
	public boolean getBoolean(String name) {
		String value = this.getString(name);
		if(     value.equalsIgnoreCase("true"))		return true;
		else if(value.equalsIgnoreCase("false"))	return false;
		else if(value.equalsIgnoreCase("on"))		return true;
		else if(value.equalsIgnoreCase("off"))		return false;
		else										return Boolean.valueOf(value);
	}




}
