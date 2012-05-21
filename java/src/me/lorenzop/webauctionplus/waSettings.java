package me.lorenzop.webauctionplus;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;

public class waSettings {

	private boolean isOk = false;
	WebAuctionPlus plugin;

	protected HashMap<String, String> settingsMap = new HashMap<String, String>();

	public waSettings(WebAuctionPlus plugin) {
		this.plugin = plugin;
		isOk = false;
	}

	public synchronized void LoadSettings(){
		isOk = false;
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
		addDefaults();
		WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Loaded " + Integer.toString(settingsMap.size()) + " settings from db");
		isOk = (settingsMap.size()!=0);
	}
	public boolean isOk() {return this.isOk;}

	// set default settings
	private void addDefaults() {
		addDefault("Version",				plugin.getDescription().getVersion().toString());
		addDefault("Currency Prefix",		"$ ");
		addDefault("Currency Postfix",		"");
		addDefault("Custom Description",	"false");
		addDefault("Language",				"en");
		addDefault("Item Packs",			"");
	}
	private void addDefault(String name, String value) {
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
	public synchronized String getString(String name) {
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

	// change setting
	public synchronized void setString(String name, String value) {
		if(!settingsMap.containsKey(name)) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Setting not found! "+name);
			return;
		}
		if(settingsMap.get(name).equals(value)) {
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Setting unchanged, matches existing. "+name);
			return;
		}
		settingsMap.put(name, value);
		Connection conn = plugin.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: Update setting: " + name);
		try {
			st = conn.prepareStatement("UPDATE `"+plugin.dataQueries.dbPrefix+"Settings` SET `value` = ? WHERE `name` = ? LIMIT 1");
			st.setString(1, value);
			st.setString(2, name);
			st.executeUpdate();
		} catch(SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update setting " + name);
			e.printStackTrace();
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
	}
	public void setInteger(String name, int value) {
		this.setString(name, Integer.toString(value));
	}
	public void setBoolean(String name, boolean value) {
		this.setString(name, Boolean.toString(value));
	}

}
