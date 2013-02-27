package com.webauctionplus.Signs;

import org.bukkit.entity.Player;
import org.bukkit.event.block.BlockBreakEvent;
import org.bukkit.event.block.SignChangeEvent;
import org.bukkit.event.player.PlayerInteractEvent;

import com.poixson.pxnCommon.SignUI.SignDAO;
import com.poixson.pxnCommon.SignUI.SignType;


public class waSignMailbox extends SignType {
	// mailbox
	private static final String SignMailBox = "MailBox";
	private static final String[] SignMailBox_aliases = {
		"Mail Box",
		"Mail",
		"Chest",
		"Inventory",
		"Inv"
	};


	@Override
	public String getType() {
		return "mailbox";
	}


	// sign clicked
	@Override
	public void onSignClick(PlayerInteractEvent event, SignDAO sign) {
	}


	// validate sign lines
	@Override
	public String onSignCreate(SignChangeEvent event) {
		Player player = event.getPlayer();
		// mailbox sign
		if(!ValidLine(event, 1, SignMailBox, SignMailBox_aliases))
			return null;
		if(!player.hasPermission("wa.sign.create.mailbox")) {
//TODO:
player.sendMessage("&4No permission.");
			CancelSign(event);
			return null;
		}
		event.setLine(2, "");
		event.setLine(3, "");
//TODO:
player.sendMessage("Created MailBox sign.");
//p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_ _sign"));
		return getType();
	}


//		// Shout sign
//		if(lines[1].equalsIgnoreCase("Shout")) {
//			if(!p.hasPermission("wa.create.sign.shout")) {
//				NoPermission(event);
//				return;
//			}
//			event.setLine(1, "Shout");
//			// line 2: radius
//			int radius = 20;
//			try {
//				radius = Integer.parseInt(lines[2]);
//			} catch (NumberFormatException ignore) {}
//			event.setLine(2, Integer.toString(radius));
//			event.setLine(3, "");
//			plugin.shoutSigns.put(sign.getLocation(), radius);
//			WebAuctionPlus.dataQueries.createShoutSign(world, radius, sign.getX(), sign.getY(), sign.getZ());
//			p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_shout_sign"));
//			return;
//		}

//		// Recent sign
//		if(lines[1].equalsIgnoreCase("Recent")) {
//			if(!p.hasPermission("wa.create.sign.recent")) {
//				NoPermission(event);
//				return;
//			}
//			// line 2: recent offset
//			int offset = 1;
//			try {
//				offset = Integer.parseInt(lines[2]);
//			} catch (NumberFormatException ignore) {}
//			if(offset < 1)  offset = 1;
//			if(offset > 10) offset = 10;
//			// display auction
//			if(offset <= WebAuctionPlus.Stats.getTotalAuctions()) {
//				Auction offsetAuction = WebAuctionPlus.dataQueries.getAuctionForOffset(offset - 1);
//				ItemStack stack = offsetAuction.getItemStack();
//				int qty = stack.getAmount();
//				String formattedPrice = plugin.economy.format(offsetAuction.getPrice());
//				event.setLine(1, stack.getType().toString());
//				event.setLine(2, "qty: "+Integer.toString(qty));
//				event.setLine(3, formattedPrice);
//			} else {
//				event.setLine(1, "Recent");
//				event.setLine(2, Integer.toString(offset));
//				event.setLine(3, "<New Sign>");
//			}
//			plugin.recentSigns.put(sign.getLocation(), offset);
//			WebAuctionPlus.dataQueries.createRecentSign(world, offset, sign.getX(), sign.getY(), sign.getZ());
//			p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_recent_sign"));
//			return;
//		}


	// sign removed
	@Override
	public void onSignRemove(BlockBreakEvent event, SignDAO sign) {








	}


}