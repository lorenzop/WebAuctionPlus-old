package me.lorenzop.webauctionplus.dao;

public class SaleAlert {

	private int alertId			= 0;
	private String buyerName	= null;
	private String itemName		= null;
	private int qty				= 0;
	private double priceEach	= 0D;
	private double priceTotal	= 0D;

	public SaleAlert() {
	}

	// alert id
	public int getAlertId() {
		return alertId;
	}
	public void setAlertId(int alertId) {
		this.alertId = alertId;
	}

	// get buyer name
	public String getBuyerName() {
		return buyerName;
	}
	public void setBuyerName(String buyerName) {
		this.buyerName = buyerName;
	}

	// item name
	public String getItemName() {
		return itemName;
	}
	public void setItem(String itemName) {
		this.itemName = itemName;
	}

	// quantity
	public int getQty() {
		return qty;
	}
	public void setQty(int qty) {
		this.qty = qty;
	}

	// price each
	public double getPriceEach() {
		return priceEach;
	}
	public void setPriceEach(double priceEach) {
		this.priceEach = priceEach;
	}

	// price total
	public double getPriceTotal() {
		return priceTotal;
	}
	public void setPriceTotal(double priceTotal) {
		this.priceTotal = priceTotal;
	}

}