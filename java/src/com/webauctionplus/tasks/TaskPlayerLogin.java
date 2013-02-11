package com.webauctionplus.tasks;

import com.poixson.pxnCommon.Task.pxnTaskThrottled;
import com.webauctionplus.WebAuctionPlus;


public class TaskPlayerLogin extends pxnTaskThrottled {

@SuppressWarnings("unused")
	private final String playerName;


	public TaskPlayerLogin(String playerName) {
		super(WebAuctionPlus.getPlugin(), "PlayerLoginTask-"+playerName);
		if(playerName == null) throw new IllegalArgumentException("playerName can't be null!");
		this.playerName = playerName;
//		this.setTaskName("PlayerLoginTask-"+playerName);
	}


	@Override
	public void runTaskThrottled() {
		try {
			// player login event
			_Update_PlayerLogin();
		} catch (Exception e) {
			log.exception(e);
		}
	}


	private void _Update_PlayerLogin() {
	}


}