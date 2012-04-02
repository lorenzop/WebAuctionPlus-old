package me.exote.webauction.listeners;

import me.exote.webauction.WebAuction;
import me.exote.webauction.dao.Auction;

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

	private final WebAuction plugin;

	public WebAuctionBlockListener(WebAuction plugin) {
		this.plugin = plugin;
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onBlockBreak(BlockBreakEvent event) {
		Block block = event.getBlock();
		Player player = event.getPlayer();
		if (block.getTypeId() == 63 || block.getTypeId() == 68) {
			Sign thisSign = (Sign) block.getState();
			if (thisSign.getLine(0).equals("[WebAuction]")) {
				if (!plugin.permission.has(player, "wa.remove")) {
					event.setCancelled(true);
					player.sendMessage(plugin.chatPrefix + "You do not have permission to remove that");
				} else {
					player.sendMessage(plugin.chatPrefix + "WebAuction sign removed.");
				}
			}
		}
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onSignChange(SignChangeEvent event) {
		String[] lines = event.getLines();
		Player player = event.getPlayer();
		Block sign = event.getBlock();
		World world = sign.getWorld();
		if (player == null) {
			return;
		}
		if (!lines[0].equalsIgnoreCase("[WebAuction]")) {
			return;
		}
		if (!lines[0].equals("[WebAuction]")) {
			event.setLine(0, "[WebAuction]");
		}
		Boolean allowEvent = false;

		// Shout sign
		if (lines[1].equalsIgnoreCase("Shout")) {
			if (plugin.permission.has(player, "wa.create.sign.shout")) {
				allowEvent = true;
				player.sendMessage(plugin.chatPrefix + "Shout sign created.");
				if (!lines[1].equals("Shout")) {
					event.setLine(1, "Shout");
				}
				// line 2: radius
				int radius = 20;
				try {
					radius = Integer.parseInt(lines[2]);
				} catch (NumberFormatException e) {
					event.setLine(2, Integer.toString(radius));
				}
				plugin.shoutSigns.put(sign.getLocation(), radius);
				plugin.dataQueries.createShoutSign(world, radius, sign.getX(), sign.getY(), sign.getZ());
			}
		} else

		// Recent sign
		if (lines[1].equalsIgnoreCase("Recent")) {
			if (plugin.permission.has(player, "wa.create.sign.recent")) {
				allowEvent = true;
				player.sendMessage(plugin.chatPrefix + "Recent auction sign created.");
				// line 2: recent offset
				int offset = 1;
				try {
					offset = Integer.parseInt(lines[2]);
				} catch (NumberFormatException nfe) {
					offset = 1;
				}
				// display auction
				int totalAuctionCount = plugin.dataQueries.getTotalAuctionCount();
				if (offset <= totalAuctionCount) {
					Auction offsetAuction = plugin.dataQueries.getAuctionForOffset(offset - 1);
					ItemStack stack = offsetAuction.getItemStack();
					int qty = stack.getAmount();
					String formattedPrice = plugin.economy.format(offsetAuction.getPrice());
					event.setLine(1, stack.getType().toString());
					event.setLine(2, Integer.toString(qty));
					event.setLine(3, formattedPrice);
				} else {
					event.setLine(1, "Recent");
					event.setLine(2, Integer.toString(offset));
					event.setLine(3, "Not Available");
				}
				plugin.recentSigns.put(sign.getLocation(), offset);
				plugin.dataQueries.createRecentSign(world, offset, sign.getX(), sign.getY(), sign.getZ());
			}
		} else

		// Deposit sign (money)
		if (lines[1].equalsIgnoreCase("Deposit")) {
			if (plugin.permission.has(player, "wa.create.sign.deposit")) {
				allowEvent = true;
				player.sendMessage(plugin.chatPrefix + "Deposit point created");
				if (!lines[1].equals("Deposit")) {
					event.setLine(1, "Deposit");
				}
			}
		} else

		// Withdraw sign (money)
		if (lines[1].equalsIgnoreCase("Withdraw")) {
			if (plugin.permission.has(player, "wa.create.sign.withdraw")) {
				allowEvent = true;
				player.sendMessage(plugin.chatPrefix + "Withdraw point created");
				if (!lines[1].equals("Withdraw")) {
					event.setLine(1,"Withdraw");
				}
			}
		} else

		// MailBox sign
		if (lines[1].equalsIgnoreCase("MailBox") ||
			lines[1].equalsIgnoreCase("Mail Box") ||
			lines[1].equalsIgnoreCase("Mail")) {
			if (!lines[1].equals("MailBox")) {
				event.setLine(1, "MailBox");
			}
			// Deposit sign (items)
			if (lines[2].equalsIgnoreCase("Deposit")) {
				if (plugin.permission.has(player, "wa.create.sign.mailbox.deposit")) {
					allowEvent = true;
					player.sendMessage(plugin.chatPrefix + "Deposit Mail Box created");
					if (!lines[2].equals("Deposit")) {
						event.setLine(2, "Deposit");
					}
				}
			} else
			// Withdraw sign (items)
			if (lines[2].equalsIgnoreCase("Withdraw")) {
				if (plugin.permission.has(player, "wa.create.sign.mailbox.withdraw")) {
					allowEvent = true;
					player.sendMessage(plugin.chatPrefix + "Withdraw Mail Box created");
					if (!lines[2].equals("Withdraw")) {
						event.setLine(2, "Withdraw");
					}
				}
			}
		}

		if (!allowEvent) {
			event.setCancelled(true);
			sign.setTypeId(0);
			ItemStack stack = new ItemStack(323, 1);
			player.getInventory().addItem(stack);
			player.sendMessage(plugin.chatPrefix + "You do not have permission");
		}
	}

}