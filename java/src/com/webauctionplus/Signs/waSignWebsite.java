package com.webauctionplus.Signs;

import org.bukkit.entity.Player;
import org.bukkit.event.block.BlockBreakEvent;
import org.bukkit.event.block.SignChangeEvent;
import org.bukkit.event.player.PlayerInteractEvent;

import com.poixson.pxnCommon.pxnUtils;
import com.poixson.pxnCommon.SignUI.SignDAO;
import com.poixson.pxnCommon.SignUI.SignType;


public class waSignWebsite extends SignType {
	// website
	private static final String SignWebsite = ""; //"&5";
	private static final String[] SignWebsite_aliases = {
		"Website",
		"Web Site",
		"Web",
		"Link",
		"URL"
	};


	@Override
	public String getType() {
		return "website";
	}


	// sign clicked
	@Override
	public boolean onSignClick(PlayerInteractEvent event, SignDAO sign) {
		Player player = event.getPlayer();
String website = "http://mc.poixson.com/";
player.sendMessage(website);
		return true;
	}


	// validate sign lines
	@Override
	public String onSignCreate(SignChangeEvent event) {
		Player player = event.getPlayer();
		// website sign
		if(!ValidLine(event, 1, SignWebsite, SignWebsite_aliases))
			return null;
		if(!player.hasPermission("wa.sign.create.website")) {
//TODO:
player.sendMessage("&4No permission.");
			CancelSign(event);
			return null;
		}
String website = "http://mc.poixson.com/";
		if(website.startsWith("http://") ) website = website.substring(7);
		if(website.startsWith("https://")) website = website.substring(8);
		if(website.endsWith("/")) website = website.substring(0, website.length()-1);
		int i;
		for(i=1; i<=3; i++) {
			int L = pxnUtils.MinMax(website.length(), 0, 15-SignWebsite.length());
			if(L == 0) break;
			event.setLine(i, pxnUtils.ReplaceColors(SignWebsite+website.substring(0, L)));
			website = website.substring(L);
			if(website.isEmpty()) break;
		}
		ClearSignAfter(i, event);
//TODO:
player.sendMessage("Created Website sign.");
//p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_ _sign"));
		return getType();
	}


	// sign removed
	@Override
	public boolean onSignRemove(BlockBreakEvent event, SignDAO sign) {







		return false;
	}


}