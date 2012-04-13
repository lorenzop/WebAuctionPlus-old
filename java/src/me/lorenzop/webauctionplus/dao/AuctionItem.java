package me.lorenzop.webauctionplus.dao;

public class AuctionItem {

	private int itemId		= 0;
	private int typeId		= 0;
	private int damage		= 0;
	private String player	= null;
	private int qty			= 0;

	public AuctionItem() {
	}

	// db item id
	public int getItemId() {
		return itemId;
	}
	public void setItemId(int itemId) {
		this.itemId = itemId;
	}

	// item type id
	public int getTypeId() {
		return typeId;
	}
	public void setTypeId(int typeId) {
		this.typeId = typeId;
	}

	// damage
	public int getDamage() {
		return damage;
	}
	public void setDamage(int damage) {
		this.damage = damage;
	}

	// player name
	public String getPlayerName() {
		return player;
	}
	public void setPlayerName(String player) {
		this.player = player;
	}

	// quantity
	public int getQty() {
		return qty;
	}
	public void setQty(int qty) {
		this.qty = qty;
	}

}