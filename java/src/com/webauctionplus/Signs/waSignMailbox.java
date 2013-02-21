package com.webauctionplus.Signs;

import org.bukkit.GameMode;
import org.bukkit.block.Block;
import org.bukkit.entity.Player;
import org.bukkit.event.block.SignChangeEvent;

import com.poixson.pxnCommon.SignUI.SignDAO;


public class waSignMailbox extends waSign {
	private static final String SignMailBox = "MailBox";
	private static final String[] SignMailBox_aliases = {
		"Mail Box",
		"Mail",
		"Chest",
		"Inventory",
		"Inv"
	};


	// validate sign lines
	@Override
	public boolean ValidateSign(SignChangeEvent event) {
		// validate first line
		if(!super.ValidateSign(event))
			return false;
		Block block = event.getBlock();
		Player player = event.getPlayer();

		// mailbox sign
		if(SignDAO.ValidLine(event, 1, SignMailBox, SignMailBox_aliases)) {
			if(!player.hasPermission("wa.sign.create.mailbox")) {
//TODO:
player.sendMessage("&4No permission.");
				event.setCancelled(true);
				return false;
			}
			event.setLine(2, "");
			event.setLine(3, "");
//TODO:
player.sendMessage("Created MailBox sign.");
//p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_deposit_mail_sign"));
			return true;
		}

		// invalid sign
//TODO:
player.sendMessage("&4Invalid sign.");
//p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("invalid_sign"));
		event.setCancelled(true);
		// break sign
		if(player.getGameMode().equals(GameMode.CREATIVE))
			block.setTypeId(0);
		else
			block.breakNaturally();

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

		return false;
	}


	// sign clicked
	@Override
	public void onClick() {
	}


}