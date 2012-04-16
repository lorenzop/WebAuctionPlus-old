package me.lorenzop.webauctionplus.dao;

public class AuctionPlayer {

	private int playerId	= 0;
	private String player	= null;
//	private String pass		= null;
	private double money	= 0D;
	private boolean canBuy	= false;
	private boolean canSell	= false;
	private boolean isAdmin	= false;

	public AuctionPlayer() {
	}
	public AuctionPlayer(String player) {
		this.player = player;
	}

	// player id
	public int getPlayerId() {
		return playerId;
	}
	public void setPlayerId(int playerId) {
		this.playerId = playerId;
	}

	// player name
	public String getPlayerName() {
		return player;
	}

	public void setPlayerName(String player) {
		this.player = player;
	}

//	public String getPass() {
//		return pass;
//	}

	// money
	public double getMoney() {
		return money;
	}
	public void setMoney(double money) {
		this.money = money;
	}

	// can buy
	public boolean getCanBuy() {
		return canBuy;
	}
//	public void setCanBuy(boolean canBuy) {
//		this.canBuy = canBuy;
//	}

	// can sell
	public boolean getCanSell() {
		return canSell;
	}
//	public void setCanSell(boolean canSell) {
//		this.canSell = canSell;
//	}

	// is admin
	public boolean getIsAdmin() {
		return isAdmin;
	}
//	public void setIsAdmin(boolean isAdmin) {
//		this.isAdmin = isAdmin;
//	}

	// player permissions
	public String getPermsString() {
		String tempPerms = "";
		if (canBuy)  tempPerms = this.addStringSet(tempPerms, "canBuy",  ",");
		if (canSell) tempPerms = this.addStringSet(tempPerms, "canSell", ",");
		if (isAdmin) tempPerms = this.addStringSet(tempPerms, "isAdmin", ",");
		return tempPerms;
	}
	public void setPerms(boolean canBuy, boolean canSell, boolean isAdmin) {
		this.canBuy  = canBuy;
		this.canSell = canSell;
		this.isAdmin = isAdmin;
	}
	public void setPerms(String Perms) {
		Perms = ","+Perms+",";
		canBuy  = Perms.contains(",canBuy,");
		canSell = Perms.contains(",canSell,");
		isAdmin = Perms.contains(",isAdmin,");
	}

	public String addStringSet(String baseString, String addThis, String Delim) {
		if (addThis.isEmpty())    return baseString;
		if (baseString.isEmpty()) return addThis;
		return baseString + Delim + addThis;
	}

}