package com.webauctionplus.Signs;

import org.bukkit.event.block.SignChangeEvent;

import com.poixson.pxnCommon.SignUI.SignPlugin;


public class waSign extends SignPlugin {
	private static final String SignFirstLine = "&1[WebAuction+]";
	private static final String[] SignFirstLine_aliases = {
		"[WebAuction+]",
		"[WebAuction]",
		"[wa]"
	};


	// validate sign lines
	@Override
	public boolean ValidateSignFirst(SignChangeEvent event) {
		return ValidLine(event, 0, SignFirstLine, SignFirstLine_aliases);
	}


	@Override
	public void InvalidSign(SignChangeEvent event) {
		// invalid sign
//TODO:
event.getPlayer().sendMessage("&4Invalid sign.");
//p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("invalid_sign"));
		event.setCancelled(true);
		// break sign
		CancelSign(event);
	}


}