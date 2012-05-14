package me.lorenzop.webauctionplus;

import java.io.File;
import java.io.InputStream;
import java.util.HashMap;

import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.configuration.file.YamlConfiguration;

public class Language {

	protected WebAuctionPlus plugin;
	protected FileConfiguration langConfig;

	protected HashMap<String, String> langMap = new HashMap<String, String>();
	protected String lang = "";

	public Language(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public synchronized void loadLanguage(String setLang) {
		if(setLang.isEmpty()) lang = "en";
		else                  lang = setLang;
		loadKeys();
		try {
			File langFile = new File(plugin.getDataFolder().toString()+File.separator+"languages"+File.separator+lang+".yml");
			langConfig = YamlConfiguration.loadConfiguration(langFile);
			// look for defaults in the jar
			InputStream defaultLangStream = plugin.getResource("languages"+File.separator+lang+".yml");
			if(defaultLangStream != null) {
				YamlConfiguration defaultLang = YamlConfiguration.loadConfiguration(defaultLangStream);
				langConfig.setDefaults(defaultLang);
			}
			// load language messages
			for(String key : langMap.keySet()) {
				langMap.put(key, langConfig.getString(key));
			}
			// save defaults
			langConfig.options().copyDefaults(true);
			langConfig.save(langFile);
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Loaded language file "+lang+".yml");
		} catch(Exception e) {
			WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix+"Failed to load language file "+lang+".yml");
			e.printStackTrace();
		}
	}

	public synchronized String getString(String key) {
		if(langMap.containsKey(key))
			if(!langMap.get(key).isEmpty())
				return langMap.get(key);
		WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Language message not found: " + key);
		return null;
	}

	private void loadKeys() {
		langMap.put("no_permission",				"");
		langMap.put("no_cheating",					"");
		langMap.put("no_item_in_hand",				"");
		langMap.put("item_stack_stored",			"");
		langMap.put("got_mail",						"");
		langMap.put("no_mail",						"");
		langMap.put("inventory_full",				"");
		langMap.put("not_enough_money_pocket",		"");
		langMap.put("not_enough_money_account",		"");
		langMap.put("reloading",					"");
		langMap.put("finished_reloading",			"");
		langMap.put("saving",						"");
		langMap.put("finished_saving",				"");
		langMap.put("account_created",				"");
		langMap.put("password_changed",				"");
		langMap.put("account_not_found",			"");
		langMap.put("created_shout_sign",			"");
		langMap.put("created_recent_sign",			"");
		langMap.put("created_deposit_sign",			"");
		langMap.put("created_withdraw_sign",		"");
		langMap.put("created_deposit_mail_sign",	"");
		langMap.put("created_withdraw_mail_sign",	"");
		langMap.put("sign_removed",					"");
	}

}
