package me.lorenzop.webauctionplus.tasks;

import java.util.ArrayList;
import java.util.List;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.Auction;

import org.bukkit.Bukkit;
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
		if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Current Auction id = "+lastAuction);
	}

	public void run() {
		// check for new auctions
		int latestAuctionID = WebAuctionPlus.Stats.getMaxAuctionID();
		if(lastAuction >= latestAuctionID) return;
		lastAuction = latestAuctionID;
		if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Current Auction id = "+lastAuction);
		if(plugin.getServer().getOnlinePlayers().length == 0) return;

		List<Location> SignsToRemove = new ArrayList<Location>();
		Auction auction = WebAuctionPlus.dataQueries.getAuction(latestAuctionID);
		Player[] playerList = plugin.getServer().getOnlinePlayers();
		ItemStack stack = auction.getItemStack();

// TODO: language here
		String msg;
		if(auction.getAllowBids()) msg = "New auction: ";
		else                             msg = "For sale: ";
		msg += Integer.toString(stack.getAmount())+"x "+auction.getItemTitle()+" ";
		if(stack.getEnchantments().size() == 1)
			msg += "(with 1 enchantment) ";
		else if(stack.getEnchantments().size() > 1)
			msg += "(with "+Integer.toString(stack.getEnchantments().size())+" enchantments) ";
		if(auction.getAllowBids())
			msg += "has started!";
		else
			msg += "selling for "+WebAuctionPlus.FormatPrice(auction.getPrice())+" each.";
		WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+msg);

		// announce globally
		if(WebAuctionPlus.announceGlobal()) {
			Bukkit.broadcastMessage(WebAuctionPlus.chatPrefix+msg);
		} else {
			// Loop each shout sign, sending the New Auction message to each
			for(Location key : plugin.shoutSigns.keySet()) {
				if(key.getBlock().getType() != Material.SIGN && key.getBlock().getType() != Material.WALL_SIGN) {
					SignsToRemove.add(key);
					continue;
				}
				Double xValue = key.getX();
				Double zValue = key.getZ();
				int radius = plugin.shoutSigns.get(key);
				for(Player player : playerList) {
					Double playerX = player.getLocation().getX();
					Double playerZ = player.getLocation().getZ();
					if( (playerX < xValue + (double)radius) &&
						(playerX > xValue - (double)radius) &&
						(playerZ < zValue + (double)radius) &&
						(playerZ > zValue - (double)radius) ) {
							player.sendMessage(WebAuctionPlus.chatPrefix+msg);
					}
				}
			}
		}

		try {
			for(Location signLoc : SignsToRemove) {
				plugin.shoutSigns.remove(signLoc);
				WebAuctionPlus.dataQueries.removeShoutSign(signLoc);
				WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Removed invalid sign at location: "+signLoc);
			}
		} catch(Exception e) {
			e.printStackTrace();
		}
	}

}