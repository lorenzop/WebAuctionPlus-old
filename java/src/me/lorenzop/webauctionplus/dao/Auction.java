package me.lorenzop.webauctionplus.dao;

import org.bukkit.inventory.ItemStack;

public class Auction {

	private int Offset			= -1;
	private ItemStack stack		= null;
	private String itemTitle	= null;
	private String player		= null;
	private double price		= 0D;
//	private long created		= 0;
	private Boolean allowBids	= false;
	private Double currentBid	= 0D;
	private String currentWinner= null;

	public Auction() {
	}

	// auction id
	public int getOffset() {
		return Offset;
	}
	public void setOffset(int Offset) {
		this.Offset = Offset;
	}

	// item stack
	public ItemStack getItemStack() {
		return stack;
	}
	public void setItemStack(ItemStack stack) {
		this.stack = stack;
	}

	// item title
	public String getItemTitle() {
		if(itemTitle == null || itemTitle.isEmpty())
			if(stack != null) return stack.getType().name();
		return itemTitle;
	}
	public void setItemTitle(String itemTitle) {
		this.itemTitle = itemTitle;
	}

	// player name
	public String getPlayerName() {
		return player;
	}
	public void setPlayerName(String player) {
		this.player = player;
	}

	// price
	public double getPrice() {
		return price;
	}
	public void setPrice(double price) {
		this.price = price;
	}

//	// created timestamp
//	public long getCreated() {
//		return created;
//	}
//	public void setCreated(long created) {
//		this.created = created;
//	}

	// allow bids ?
	public Boolean getAllowBids() {
		return allowBids;
	}
	public void setAllowBids(Boolean bid) {
		this.allowBids = bid;
	}

	// current bid ?
	public Double getCurrentBid() {
		return currentBid;
	}
	public void setCurrentBid(Double bid) {
		this.currentBid = bid;
	}

	// current winner ?
	public String getCurrentWinner() {
		return currentWinner;
	}
	public void setCurrentWinner(String player) {
		this.currentWinner = player;
	}

}