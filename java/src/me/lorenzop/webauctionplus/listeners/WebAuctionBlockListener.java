package me.lorenzop.webauctionplus.listeners;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.Auction;

import org.bukkit.World;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.block.BlockBreakEvent;
import org.bukkit.event.block.SignChangeEvent;
import org.bukkit.inventory.ItemStack;

public class WebAuctionBlockListener implements Listener {

	private final WebAuctionPlus plugin;

	public WebAuctionBlockListener(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onBlockBreak(BlockBreakEvent event) {
		Block block = event.getBlock();
		Player p = event.getPlayer();
		if(block.getTypeId() == 63 || block.getTypeId() == 68) {
			Sign thisSign = (Sign) block.getState();
			if(thisSign.getLine(0).equals("[WebAuction+]")) {
				if(!p.hasPermission("wa.remove")) {
					event.setCancelled(true);
					p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				} else {
					p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("sign_removed"));
					WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + WebAuctionPlus.Lang.getString("sign_removed"));
				}
			}
		}
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onSignChange(SignChangeEvent event) {
		String[] lines = event.getLines();
		Player p = event.getPlayer();
		Block sign = event.getBlock();
		World world = sign.getWorld();
		if(p == null) return;
		if (!lines[0].equalsIgnoreCase("[WebAuction]") &&
			!lines[0].equalsIgnoreCase("[WebAuction+]") &&
			!lines[0].equalsIgnoreCase("[wa]") ) return;
		event.setLine(0, "[WebAuction+]");
		boolean allowEvent = false;

		// Shout sign
		if(lines[1].equalsIgnoreCase("Shout")) {
			if(p.hasPermission("wa.create.sign.shout")) {
				allowEvent = true;
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_shout_sign"));
				event.setLine(1, "Shout");
				// line 2: radius
				int radius = 20;
				try {
					radius = Integer.parseInt(lines[2]);
				} catch (NumberFormatException ignore) {}
				event.setLine(2, Integer.toString(radius));
				event.setLine(3, "");
				plugin.shoutSigns.put(sign.getLocation(), radius);
				WebAuctionPlus.dataQueries.createShoutSign(world, radius, sign.getX(), sign.getY(), sign.getZ());
			}
		} else

		// Recent sign
		if(lines[1].equalsIgnoreCase("Recent")) {
			if (p.hasPermission("wa.create.sign.recent")) {
				allowEvent = true;
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_recent_sign"));
				// line 2: recent offset
				int offset = 1;
				try {
					offset = Integer.parseInt(lines[2]);
				} catch (NumberFormatException ignore) {}
				if(offset < 1)  offset = 1;
				if(offset > 10) offset = 10;
				// display auction
				if(offset <= WebAuctionPlus.Stats.getTotalAuctions()) {
					Auction offsetAuction = WebAuctionPlus.dataQueries.getAuctionForOffset(offset - 1);
					ItemStack stack = offsetAuction.getItemStack();
					int qty = stack.getAmount();
					String formattedPrice = plugin.economy.format(offsetAuction.getPrice());
					event.setLine(1, stack.getType().toString());
					event.setLine(2, "qty: "+Integer.toString(qty));
					event.setLine(3, formattedPrice);
				} else {
					event.setLine(1, "Recent");
					event.setLine(2, Integer.toString(offset));
					event.setLine(3, "Not Available");
				}
				plugin.recentSigns.put(sign.getLocation(), offset);
				WebAuctionPlus.dataQueries.createRecentSign(world, offset, sign.getX(), sign.getY(), sign.getZ());
			}
		} else

		// Deposit sign (money)
		if(lines[1].equalsIgnoreCase("Deposit")) {
			if (p.hasPermission("wa.create.sign.deposit")) {
				allowEvent = true;
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_deposit_sign"));
				event.setLine(1, "Deposit");
				// line 2: amount
				double amount = 100;
				try {
					amount = WebAuctionPlus.ParseDouble(lines[2]);
					if(amount <= 0D) amount = 100D;
				} catch(NumberFormatException ignore) {}
				event.setLine(2, WebAuctionPlus.FormatPrice(amount));
				event.setLine(3, "");
			}
		} else

		// Withdraw sign (money)
		if(lines[1].equalsIgnoreCase("Withdraw")) {
			if (p.hasPermission("wa.create.sign.withdraw")) {
				allowEvent = true;
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_withdraw_sign"));
				if(!lines[1].equals("Withdraw")) event.setLine(1,"Withdraw");
				// line 2: amount
				double amount = 0;
				if(!lines[2].equalsIgnoreCase("all")) {
					try {
						amount = WebAuctionPlus.ParseDouble(lines[2]);
						if(amount < 0D) amount = 0D;
					} catch(NumberFormatException ignore) {}
				}
				event.setLine(2, amount==0 ? "All" : WebAuctionPlus.FormatPrice(amount) );
				event.setLine(3, "");
			}
		} else

		// MailBox sign
		if(lines[1].equalsIgnoreCase("MailBox") ||
			lines[1].equalsIgnoreCase("Mail Box") ||
			lines[1].equalsIgnoreCase("Mail")) {
			if (p.hasPermission("wa.create.sign.mailbox")) {
				allowEvent = true;
				event.setLine(1, "MailBox");
				event.setLine(2, "");
				event.setLine(3, "");
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("created_deposit_mail_sign"));
			}
		}

		if(allowEvent) {
//TODO: this needs to be tested
			p.sendMessage(WebAuctionPlus.chatPrefix + "Invalid sign parameters! Please check dev bukkit for the right sign usage.");
		} else {
			event.setCancelled(true);
			sign.setTypeId(0);
			ItemStack stack = new ItemStack(323, 1);
			p.getInventory().addItem(stack);
			WebAuctionPlus.doUpdateInventory(p);
			p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
		}
	}

}