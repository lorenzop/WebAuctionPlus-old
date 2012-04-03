package me.exote.webauctionplus.dao;

import org.bukkit.inventory.ItemStack;

public class Auction {

	private int id;
	private ItemStack itemStack;
	private String playerName;
	private double price;
	private int created;
	private Boolean allowBids;
	private Double currentBid;
	private String currentWinner;

	public Auction() {
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public ItemStack getItemStack() {
		return itemStack;
	}

	public void setItemStack(ItemStack itemStack) {
		this.itemStack = itemStack;
	}

	public String getPlayerName() {
		return playerName;
	}

	public void setPlayerName(String playerName) {
		this.playerName = playerName;
	}

	public double getPrice() {
		return price;
	}

	public void setPrice(double price) {
		this.price = price;
	}

	public int getCreated() {
		return created;
	}

	public void setCreated(int created) {
		this.created = created;
	}

	public Boolean getAllowBids() {
		return allowBids;
	}

	public void setAllowBids(Boolean bid) {
		this.allowBids = bid;
	}

	public Double getCurrentBid() {
		return currentBid;
	}

	public void setCurrentBid(Double bid) {
		this.currentBid = bid;
	}

	public String getCurrentWinner() {
		return currentWinner;
	}

	public void setCurrentWinner(String player) {
		this.currentWinner = player;
	}

}