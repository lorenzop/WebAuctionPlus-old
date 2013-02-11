package com.webauctionplus.tasks;

import org.bukkit.Bukkit;
import org.bukkit.entity.Player;

import com.poixson.pxnCommon.pxnTask;
import com.poixson.pxnCommon.pxnUtils;


public class taskBalanceUpdate extends pxnTask {


	public taskBalanceUpdate(String taskName) {
		super(taskName);
	}


	@Override
	public void run() {
		// skip if already running
		if(!getLock()) return;
		try {
			// process asjustments queue
			_Update_BalanceAdjust();
		} catch (Exception e) {
			getLogger().exception(e);
		}
		try {
			// update online player balances
			_Update_PlayersOnline();
		} catch (Exception e) {
			getLogger().exception(e);
		}
		// done
		releaseLock();
	}


	// process asjustments queue
	private void _Update_BalanceAdjust() {
//table: PSM_BalanceAdjust
	}


	// update online player balances
	private void _Update_PlayersOnline() {
		int i = 0;
		Player[] playersOnline = Bukkit.getOnlinePlayers();
		for(Player player : playersOnline) {
			if(!player.isOnline()) continue;
			// sleep thread
			i++; if(i > 2) { i = 0;
				pxnUtils.Sleep(100);
			}
			_Update_PlayerBalance();
		}
	}


	// update a players balance
	private void _Update_PlayerBalance() {
//table: PSM_Users		
	}


}