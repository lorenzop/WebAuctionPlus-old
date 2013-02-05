package com.webauctionplus.tasks;

import com.poixson.pxnCommon.pxnTask;


public class taskPlayerUpdate extends pxnTask {


	public taskPlayerUpdate() {
		super("PlayerUpdateTask");
	}


	@Override
	public void run() {
		// skip if already running
		if(!getLock()) return;
		try {
			// player login event
			_Update_Player();
		} catch (Exception e) {
			getLogger().exception(e);
		}
		// done
		releaseLock();
	}


	private void _Update_Player() {
	}


}