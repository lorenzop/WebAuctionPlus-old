package com.webauctionplus.Signs;

import org.bukkit.event.block.SignChangeEvent;

import com.poixson.pxnCommon.SignUI.SignDAO;
import com.poixson.pxnCommon.SignUI.SignHandler;


public abstract class waSign implements SignHandler {
	private static final String SignFirstLine = "&1[WebAuction+]";
	private static final String[] SignFirstLine_aliases = {
		"[WebAuction+]",
		"[WebAuction]",
		"[wa]"
	};


	// validate sign lines
	@Override
	public boolean ValidateSign(SignChangeEvent event) {
		return SignDAO.ValidLine(event, 0, SignFirstLine, SignFirstLine_aliases);
	}


//	// sign clicked
//	@Override
//	public void onClick() {
//	}


}