package me.lorenzop.webauctionplus.tasks;

import java.util.ArrayList;
import java.util.List;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.Auction;

import org.bukkit.Location;
import org.bukkit.Material;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;

public class ShoutSignTask implements Runnable {

	private int lastAuction;

	private final WebAuctionPlus plugin;

	public ShoutSignTask(WebAuctionPlus plugin) {
		this.plugin = plugin;
		// Get current auction ID
		lastAuction = WebAuctionPlus.Stats.getMaxAuctionID();
		if(WebAuctionPlus.isDev()) WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Current Auction id = " + lastAuction);
	}

	public void run() {
		// check for new auctions
		int latestAuctionID = WebAuctionPlus.Stats.getMaxAuctionID();
		if (lastAuction >= latestAuctionID) return;
		lastAuction = latestAuctionID;
		if(WebAuctionPlus.isDev()) WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Current Auction id = " + lastAuction);
		if (plugin.getServer().getOnlinePlayers().length == 0) return;

		List<Location> toRemove = new ArrayList<Location>();
		Auction latestAuction = WebAuctionPlus.dataQueries.getAuction(latestAuctionID);
		Player[] playerList = plugin.getServer().getOnlinePlayers();
		ItemStack stack = latestAuction.getItemStack();
		String formattedPrice = plugin.economy.format(latestAuction.getPrice());

// TODO: language here
		WebAuctionPlus.log.info(
			WebAuctionPlus.logPrefix + "New Auction: " +
			stack.getAmount() + " " + stack.getType() + " selling for " +
			formattedPrice + " each.");
		String message =
			WebAuctionPlus.chatPrefix + "New Auction: " +
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

		for (Location signLoc : toRemove) {
			plugin.shoutSigns.remove(signLoc);
			WebAuctionPlus.dataQueries.removeShoutSign(signLoc);
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Removed invalid sign at location: " + signLoc);
		}
	}

}