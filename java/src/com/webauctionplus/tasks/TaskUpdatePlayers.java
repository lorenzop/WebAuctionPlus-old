package com.webauctionplus.tasks;

import java.util.HashMap;

import com.poixson.pxnCommon.Task.pxnTaskThrottled;
import com.webauctionplus.WebAuctionPlus;


public class TaskUpdatePlayers extends pxnTaskThrottled {

@SuppressWarnings("unused")
	private HashMap<String, Double> playerBalances = new HashMap<String, Double>();


	public TaskUpdatePlayers() {
		super(WebAuctionPlus.getPlugin(), "Update Players");
//TODO:
//set task interval
//set task sleep
	}


	@Override
	public void runTaskThrottled() {
//		db = getDB();
		try {
			// process adjustments queue
//			_Update_BalanceAdjust();
		} catch (Exception e) {
			log.exception(e);
		}
		try {
			// update online player balances
//			_Update_PlayersOnline();
		} catch (Exception e) {
			log.exception(e);
		}
//		db.releaseLock();
	}


//	// process adjustments queue
//	private void _Update_BalanceAdjust() {
////table: PSM_BalanceAdjust
//	}


//	// update online player balances
//	private void _Update_PlayersOnline() {
//		synchronized(playerBalances) {
//			Player[] playersOnline = Bukkit.getOnlinePlayers();
//			for(Player player : playersOnline) {
//				if(!player.isOnline()) continue;
//				// sleep thread
//				SleepTaskLoop();
//				_Update_PlayerBalance(player);
//			}
//			List<Player> playersOnlineList = new ArrayList<Player>(Arrays.asList(playersOnline));
//			// remove offline
//			List<String> removing = new ArrayList<String>();
//			for(String playerName : playerBalances.keySet())
//				if(!playersOnlineList.contains(playerName))
//					removing.add(playerName);
//			for(String playerName : removing)
//				playerBalances.remove(playerName);
//		}
//	}


//	// update a players balance
//	private void _Update_PlayerBalance(Player player) {
//		Economy economy = getEconomy();
//		String playerName = player.getName();
//		double newBalance = economy.getBalance(playerName);
//		double oldBalance = 0.0;
//		if(playerBalances.containsKey(playerName))
//			oldBalance = playerBalances.get(playerName);
//		else
//			oldBalance = _getPlayerBalance(playerName);
//		// no update needed
//		if(newBalance == oldBalance)
//			return;
//System.out.println("new balance for "+playerName+" old:"+Double.toString(oldBalance)+" new:"+newBalance );
////table: PSM_Users
//		_setPlayerBalance(playerName, newBalance);
//	}


//	private double _getPlayerBalanceCached(String playerName) {
//		if(playerBalances.containsKey(playerName))
//			return playerBalances.get(playerName);
//		return 0.0;
//	}


//	private double _getPlayerBalance(String playerName) {
//		db.Prepare("SELECT `balance` FROM `PSM_Users` WHERE `username` = ? LIMIT 1")
//			.setString(1, playerName)
//			.Exec();
//		if(!db.Next())
//return 0.0;
//		playerBalances.put(playerName, db.getDouble("balance"));
//		return db.getDouble("balance");
//	}
//	private boolean _setPlayerBalance(String playerName, double balance) {
//		playerBalances.put(playerName, balance);
//		db.Prepare("UPDATE `PSM_Users` SET `balance` = ? WHERE `username` = ? LIMIT 1")
//			.setDouble(1, balance)
//			.setString(2, playerName)
//			.Exec();
//return true;
//	}


//	// database
//	protected dbPoolConn getDB() {
//		return WebAuctionPlus.getPlugin().getDB();
//	}
//	// temp db var
//	protected dbPoolConn db = null;


//	// economy
//	private static Economy economy = null;
//	protected static Economy getEconomy() {
//		if(economy == null)
//			economy = WebAuctionPlus.getPlugin().getEconomy();
//		return economy;
//	}


}