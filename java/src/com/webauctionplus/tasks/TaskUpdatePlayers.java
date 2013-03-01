package com.webauctionplus.tasks;

import java.util.HashMap;

import com.poixson.pxnCommon.Task.pxnTaskThrottled;
import com.webauctionplus.WebAuctionPlus;


public class TaskUpdatePlayers extends pxnTaskThrottled {

@SuppressWarnings("unused")
	private HashMap<String, Double> playerBalances = new HashMap<String, Double>();


	public TaskUpdatePlayers() {
		super(WebAuctionPlus.getPlugin(), "Update Players");
	}


	@Override
	public void runTaskThrottled() {
	}


}