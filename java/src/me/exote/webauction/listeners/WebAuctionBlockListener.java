package me.exote.webauction.listeners;

import me.exote.webauction.WebAuction;
import me.exote.webauction.dao.Auction;

import org.bukkit.World;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.entity.Player;
import org.bukkit.event.block.BlockBreakEvent;
import org.bukkit.event.block.BlockListener;
import org.bukkit.event.block.SignChangeEvent;
import org.bukkit.inventory.ItemStack;

public class WebAuctionBlockListener extends BlockListener {

	private final WebAuction plugin;

	public WebAuctionBlockListener(WebAuction plugin) {
		this.plugin = plugin;
	}

	@Override
	public void onBlockBreak(BlockBreakEvent event) {
		Block block = event.getBlock();
		Player player = event.getPlayer();
		if ((block.getTypeId() == 63) || (block.getTypeId() == 68)) {
			Sign thisSign = (Sign) block.getState();
			if (thisSign.getLine(0).equals("[WebAuction]")) {
				if (!plugin.permission.has(player, "wa.remove")) {
					event.setCancelled(true);
					player.sendMessage(plugin.logPrefix + "You do not have permission to remove that");
				} else {
					player.sendMessage(plugin.logPrefix + "WebAuction sign removed.");
				}
			}
		}
	}

	public void onSignChange(SignChangeEvent event) {
		String[] lines = event.getLines();
		Player player = event.getPlayer();
		Block sign = event.getBlock();
		World world = sign.getWorld();
		Boolean allowEvent = false;
		if (player != null) {
			if (lines[0].equals("[WebAuction]")) {
				if (lines[1].equals("Shout")) {
					if (plugin.permission.has(player, "wa.create.sign.shout")) {
						allowEvent = true;
						player.sendMessage(plugin.logPrefix + "Shout sign created.");

						int radius = 20;
						try {
							radius = Integer.parseInt(lines[2]);
						} catch (NumberFormatException e) {
							event.setLine(2, Integer.toString(radius));
						}

						plugin.shoutSigns.put(sign.getLocation(), radius);
						plugin.dataQueries.createShoutSign(world, radius, sign.getX(), sign.getY(), sign.getZ());
					}
				}
				if (lines[1].equals("Recent")) {
					if (plugin.permission.has(player, "wa.create.sign.recent")) {
						allowEvent = true;
						player.sendMessage(plugin.logPrefix + "Recent auction sign created.");
						int offset = 1;
						try {
							offset = Integer.parseInt(lines[2]);
						} catch (NumberFormatException nfe) {
							offset = 1;
						}

						int totalAuctionCount = plugin.dataQueries.getTotalAuctionCount();

						if (offset <= totalAuctionCount) {
							Auction offsetAuction = plugin.dataQueries.getAuctionForOffset(offset - 1);

							ItemStack stack = offsetAuction.getItemStack();
							int qty = stack.getAmount();
							String formattedPrice = plugin.economy.format(offsetAuction.getPrice());

							event.setLine(1, stack.getType().toString());
							event.setLine(2, qty + "");
							event.setLine(3, "" + formattedPrice);
						} else {
							event.setLine(1, "Recent");
							event.setLine(2, offset + "");
							event.setLine(3, "Not Available");
						}

						plugin.recentSigns.put(sign.getLocation(), offset);
						plugin.dataQueries.createRecentSign(world, offset, sign.getX(), sign.getY(), sign.getZ());
					}
				}
				if (lines[1].equals("Deposit")) {
					if (plugin.permission.has(player, "wa.create.sign.deposit")) {
						allowEvent = true;
						player.sendMessage(plugin.logPrefix + "Deposit point created");
					}

				}
				if (lines[1].equals("Withdraw")) {
					if (plugin.permission.has(player, "wa.create.sign.withdraw")) {
						allowEvent = true;
						player.sendMessage(plugin.logPrefix + "Withdraw point created");
					}
				}
				if ((lines[1].equals("MailBox")) || (lines[1].equals("Mailbox")) || (lines[1].equals("Mail Box"))) {
					if (lines[2].equals("Deposit")) {
						if (plugin.permission.has(player, "wa.create.sign.mailbox.deposit")) {
							allowEvent = true;
							player.sendMessage(plugin.logPrefix + "Deposit Mail Box created");
						}
					} else {
						if (plugin.permission.has(player, "wa.create.sign.mailbox.withdraw")) {
							allowEvent = true;
							player.sendMessage(plugin.logPrefix + "Withdraw Mail Box created");
						}
					}
				}
				if (allowEvent == false) {
					event.setCancelled(true);
					sign.setTypeId(0);
					ItemStack stack = new ItemStack(323, 1);
					player.getInventory().addItem(stack);
					player.sendMessage(plugin.logPrefix + "You do not have permission");
				}
			}
		}
	}

}
