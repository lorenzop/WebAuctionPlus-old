package me.exote.webauction.tasks;

import java.util.ArrayList;
import java.util.List;

import me.exote.webauction.WebAuction;
import me.exote.webauction.dao.Auction;

import org.bukkit.Location;
import org.bukkit.Material;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;

public class ShoutSignTask implements Runnable {

	private final WebAuction plugin;
	private int lastAuction;

	public ShoutSignTask(WebAuction plugin) {
		this.plugin = plugin;

		// Get current auction ID
		lastAuction = plugin.dataQueries.getMaxAuctionID();
		plugin.log.info(plugin.logPrefix + "Current Auction id = " + lastAuction);
	}

	public void run() {
		List<Location> toRemove = new ArrayList<Location>();

		int latestAuctionID = plugin.dataQueries.getMaxAuctionID();

		if (lastAuction < latestAuctionID) {
			lastAuction = latestAuctionID;

			Auction latestAuction = plugin.dataQueries.getAuction(latestAuctionID);

			Player[] playerList = plugin.getServer().getOnlinePlayers();

			ItemStack stack = latestAuction.getItemStack();
			String formattedPrice = plugin.economy.format(latestAuction.getPrice());

			plugin.log.info(
				plugin.logPrefix + "New Auction: " +
				stack.getAmount() + " " + stack.getType() + " selling for " +
				formattedPrice + " each.");
			String message =
				plugin.chatPrefix + "New Auction: " +
				stack.getAmount() + " " + stack.getType() + " selling for " +
				formattedPrice + " each.";


			// Loop each shout sign, sending the New Auction message to each
			for (Location key : plugin.shoutSigns.keySet()) {
				if (key.getBlock().getType() == Material.SIGN_POST || key.getBlock().getType() == Material.WALL_SIGN) {
					Double xValue = key.getX();
					Double zValue = key.getZ();
					int radius = (Integer)plugin.shoutSigns.get(key);
					for (Player player : playerList) {
						Double playerX = player.getLocation().getX();
						Double playerZ = player.getLocation().getZ();
						if ((playerX < xValue + (double)radius) &&
							(playerX > xValue - (double)radius) &&
							(playerZ < zValue + (double)radius) &&
							(playerZ > zValue - (double)radius)) {
								player.sendMessage(message);
						}
					}
				} else {
					toRemove.add(key);
				}
			}
		}
		for (Location signLoc : toRemove) {
			plugin.shoutSigns.remove(signLoc);
			plugin.dataQueries.removeShoutSign(signLoc);
			plugin.log.info(plugin.logPrefix + "Removed invalid sign at location: " + signLoc);
		}
	}

}