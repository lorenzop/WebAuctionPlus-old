package me.exote.webauctionplus.dao;

public class AuctionPlayer {

	private int id;
	private String name;
	private String pass;
	private double money;
	private boolean canBuy;
	private boolean canSell;
	private boolean isAdmin;

	public AuctionPlayer() {
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public String getPass() {
		return pass;
	}

	public double getMoney() {
		return money;
	}

	public void setMoney(double money) {
		this.money = money;
	}

	public boolean getCanBuy() {
		return canBuy;
	}

	public void setCanBuy(boolean canBuy) {
		this.canBuy = canBuy;
	}

	public boolean getCanSell() {
		return canSell;
	}

	public void setCanSell(boolean canSell) {
		this.canSell = canSell;
	}

	public boolean getIsAdmin() {
		return isAdmin;
	}

	public void setIsAdmin(boolean isAdmin) {
		this.isAdmin = isAdmin;
	}

}