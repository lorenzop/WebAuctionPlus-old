package me.exote.webauctionplus.tasks;

import java.util.List;

import me.exote.webauctionplus.WebAuctionPlus;
import me.exote.webauctionplus.dao.SaleAlert;

import org.bukkit.entity.Player;

public class SaleAlertTask implements Runnable {

	private final WebAuctionPlus plugin;

	public SaleAlertTask(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public void run() {
		Player[] playerList = plugin.getServer().getOnlinePlayers();
		for (Player player : playerList) {
			List<SaleAlert> newSaleAlerts = plugin.dataQueries.getNewSaleAlertsForSeller(player.getName());
			for (SaleAlert saleAlert : newSaleAlerts) {
				String formattedPrice = plugin.economy.format(saleAlert.getPriceEach());
				player.sendMessage(plugin.chatPrefix + "You sold " +
					saleAlert.getQuantity() + " " + saleAlert.getItem() +
					" to " + saleAlert.getBuyer() + " for " + formattedPrice + " each.");
				plugin.dataQueries.markSaleAlertSeen(saleAlert.getId());
			}
		}
	}

}