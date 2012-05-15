package me.lorenzop.webauctionplus.tasks;

import java.util.List;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.SaleAlert;

import org.bukkit.entity.Player;

public class SaleAlertTask implements Runnable {

	private final WebAuctionPlus plugin;

	public SaleAlertTask(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public void run() {
		if (plugin.getServer().getOnlinePlayers().length == 0) return;

		Player[] playerList = plugin.getServer().getOnlinePlayers();
		for (Player player : playerList) {
			List<SaleAlert> newSaleAlerts = plugin.dataQueries.getNewSaleAlertsForSeller(player.getName());
			for (SaleAlert saleAlert : newSaleAlerts) {
				String formattedPrice = plugin.economy.format(saleAlert.getPriceEach());
// TODO: language here
				player.sendMessage(WebAuctionPlus.chatPrefix + "You sold " +
					saleAlert.getQty() + " " + saleAlert.getItemName() +
					" to " + saleAlert.getBuyerName() + " for " + formattedPrice + " each.");
				plugin.dataQueries.markSaleAlertSeen(saleAlert.getAlertId());
			}
		}
	}

}