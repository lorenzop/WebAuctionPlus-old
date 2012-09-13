package me.lorenzop.webauctionplus;

import java.io.File;
import java.io.InputStream;
import java.util.HashMap;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.configuration.file.YamlConfiguration;

public class Language {

	protected HashMap<String, String> langMap = new HashMap<String, String>();
	protected FileConfiguration langConfig;

	protected final WebAuctionPlus plugin;
	private boolean isOk;

	public Language(WebAuctionPlus plugin) {
		this.plugin = plugin;
		isOk = false;
		loadKeys();
	}

	// load language yml
	public synchronized void loadLanguage(String lang) {
		isOk = false;
		if(lang==null || lang.isEmpty()) lang = "en";
		if(lang.length() != 2) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Language should only be 2 letters! "+lang);
			return;
		}
		// try loading language file
		loadLanguageFile(lang);
		if(isOk) return;
		if(!lang.equals("en")) {
			// failed to load, so load default en.yml
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Defaulting to en.yml");
			loadLanguageFile("en");
			if(isOk) return;
		}
		WebAuctionPlus.log.severe("Failed to load language! "+lang);
	}
	public boolean isOk() {return this.isOk;}

	private void loadLanguageFile(String lang) {
		try {
			// load from plugins folder
			File langFile = new File(plugin.getDataFolder().toString()+File.separator+"languages"+File.separator+lang+".yml");
			if(langFile.exists() && !langFile.canWrite())
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"File is not writable! "+langFile.toString());
			langConfig = YamlConfiguration.loadConfiguration(langFile);
			// look for defaults in the jar
			InputStream defaultLangStream = plugin.getClass().getClassLoader().getResourceAsStream("languages/"+lang+".yml");
			if(defaultLangStream == null) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Language file not found in jar: "+lang+".yml");
				if(!langFile.exists()) return;
			} else {
				YamlConfiguration defaultLangConfig = YamlConfiguration.loadConfiguration(defaultLangStream);
				// copy defaults
				langConfig.setDefaults(defaultLangConfig);
			}
			// load language messages
			for(String key : langMap.keySet()) {
				langMap.put(key, langConfig.getString(key));
			}
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Loaded language file "+lang+".yml");
			// save defaults
			langConfig.options().copyDefaults(true);
//			langConfig.save(langFile);
		} catch(Exception e) {
//			WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix+"Failed to load language file "+lang+".yml");
			e.printStackTrace();
			return;
		}
		isOk = true;
	}

	public synchronized String getString(String key) {
		if(!isOk) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"No language file has been loaded!");
		} else if(langMap.containsKey(key)) {
			String value = langMap.get(key);
			if(value!=null && !value.isEmpty())
				return value;
		}
		WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Language message not found: " + key);
		return "<<Message not found!>>";
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
		langMap.put("invalid_sign",					"");
		langMap.put("mailbox_title",				"");
		langMap.put("mailbox_opened",				"");
		langMap.put("mailbox_closed",				"");
		langMap.put("please_wait",					"");
		langMap.put("removed_enchantments",			"");
	}

}