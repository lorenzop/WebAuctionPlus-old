package me.lorenzop.webauctionplus;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;

public class waSettings {

	protected HashMap<String, String> settingsMap = new HashMap<String, String>();

	private final WebAuctionPlus plugin;
	private boolean isOk;

	public waSettings(WebAuctionPlus plugin) {
		this.plugin = plugin;
		isOk = false;
	}


	public synchronized void LoadSettings(){
		isOk = false;
		Connection conn = WebAuctionPlus.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: LoadSettings");
			st = conn.prepareStatement("SELECT `name`, `value` FROM `"+WebAuctionPlus.dataQueries.dbPrefix()+"Settings`");
			rs = st.executeQuery();
			while (rs.next()) {
				if(rs.getString(1) != null)
					settingsMap.put(rs.getString(1), rs.getString(2));
			}
			updateSettingsTable();
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get settings");
			e.printStackTrace();
			return;
		} finally {
			WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
		}
		addDefaults();
		WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Loaded " + Integer.toString(settingsMap.size()) + " settings from db");
		isOk = (settingsMap.size()!=0);
	}
	public boolean isOk() {return this.isOk;}


	// set default settings
	private void addDefaults() {
		addDefault("Version",				plugin.getDescription().getVersion().toString());
		addDefault("Language",				"en");
		addDefault("Require Login",			false);
		addDefault("CSRF Protection",		true);
		addDefault("Currency Prefix",		"$ ");
		addDefault("Currency Postfix",		"");
		addDefault("Custom Description",	false);
		addDefault("Inventory Rows",		6);
		addDefault("Website Theme",			"");
		addDefault("jQuery UI Pack",		"");
		addDefault("Item Packs",			"");
		addDefault("Max Sell Price",		10000.00);
//		addDefault("Max Selling Per Player",20);
//		addDefault("Storage base per stack",1.0);
//		addDefault("Storage add per item",	0.1);
	}
	private void addDefault(String name, String value) {
		if(!settingsMap.containsKey(name)) {
//			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: Insert setting: " + name);
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Adding default setting for: " + name);
			Connection conn = WebAuctionPlus.dataQueries.getConnection();
			PreparedStatement st = null;
			ResultSet rs = null;
			try {
				st = conn.prepareStatement("INSERT INTO `"+WebAuctionPlus.dataQueries.dbPrefix()+"Settings` (`name`,`value`) VALUES (?, ?)");
				st.setString(1, name);
				st.setString(2, value);
				st.executeUpdate();
				settingsMap.put(name, value);
			} catch (SQLException e) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to add setting: " + name);
				e.printStackTrace();
			} finally {
				WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
			}
		}
	}
	private void addDefault(String name, boolean value) {
		if(value) addDefault(name, "true");
		else      addDefault(name, "false");
	}
	private void addDefault(String name, int value) {
		addDefault(name, Integer.toString(value));
	}
//	private void addDefault(String name, long value) {
//		addDefault(name, Long.toString(value));
//	}
	private void addDefault(String name, double value) {
		addDefault(name, Double.toString(value));
	}


	private void updateSettingsTable() {
		if(settingsMap.containsKey("jquery ui pack")) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Updating Settings table: jQuery UI Pack");
			WebAuctionPlus.dataQueries.executeRawSQL("UPDATE `"+WebAuctionPlus.dataQueries.dbPrefix()+"Settings` SET `name` = 'jQuery UI Pack' WHERE `name` = 'jquery ui pack' LIMIT 1");
			settingsMap.put("jQuery UI Pack", "");
		}
	}


	// get setting
	public synchronized String getString(String name) {
		if(settingsMap.containsKey(name))
			return settingsMap.get(name);
		else
			return null;
	}
	public boolean getBoolean(String name) {
		String value = this.getString(name);
		if(     value.equalsIgnoreCase("true"))		return true;
		else if(value.equalsIgnoreCase("false"))	return false;
		else if(value.equalsIgnoreCase("on"))		return true;
		else if(value.equalsIgnoreCase("off"))		return false;
		else										return Boolean.valueOf(value);
	}
	public int getInteger(String name) {
		return Integer.valueOf(this.getString(name));
	}
	public double getDouble(String name) {
		return Double.valueOf(this.getString(name));
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
		Connection conn = WebAuctionPlus.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		if (WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: Update setting: " + name);
		try {
			st = conn.prepareStatement("UPDATE `"+WebAuctionPlus.dataQueries.dbPrefix()+"Settings` SET `value` = ? WHERE `name` = ? LIMIT 1");
			st.setString(1, value);
			st.setString(2, name);
			st.executeUpdate();
		} catch(SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update setting " + name);
			e.printStackTrace();
		} finally {
			WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
		}
	}
	public void setInteger(String name, int value) {
		this.setString(name, Integer.toString(value));
	}
	public void setBoolean(String name, boolean value) {
		this.setString(name, Boolean.toString(value));
	}


}
