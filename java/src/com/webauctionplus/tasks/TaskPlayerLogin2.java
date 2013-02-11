package com.webauctionplus.tasks;

import com.poixson.pxnCommon.pxnTask;


public class taskPlayerLogin extends pxnTask {

@SuppressWarnings("unused")
	private final String playerName;


	public taskPlayerLogin(String playerName) {
		super("PlayerLoginTask");
		if(playerName == null) throw new IllegalArgumentException("playerName can't be null!");
		this.playerName = playerName;
		this.setTaskName("PlayerLoginTask-"+playerName);
	}


	@Override
	public void run() {
		// skip if already running
		if(!getLock()) return;
		try {
			// player login event
			_Update_PlayerLogin();
		} catch (Exception e) {
			getLogger().exception(e);
		}
		// done
		releaseLock();
	}


	private void _Update_PlayerLogin() {
	}


}