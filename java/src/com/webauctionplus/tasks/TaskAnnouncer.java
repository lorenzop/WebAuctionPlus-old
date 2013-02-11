package com.webauctionplus.tasks;

import com.poixson.pxnCommon.uniqueHistoryRND;
import com.poixson.pxnCommon.Task.pxnTask;
import com.webauctionplus.WebAuctionPlus;


public class TaskAnnouncer extends pxnTask {

	private uniqueHistoryRND rnd;


	public TaskAnnouncer() {
		super(WebAuctionPlus.getPlugin(), "Announcer", false, false);
		rnd = new uniqueHistoryRND(0, 9);
	}


	@Override
	protected void runTask() {
for(int i=0; i<20; i++) {
		int index = rnd.RND();
WebAuctionPlus.getPlugin().getLog().warning("Prob", Integer.toString(index));
}
	}


}