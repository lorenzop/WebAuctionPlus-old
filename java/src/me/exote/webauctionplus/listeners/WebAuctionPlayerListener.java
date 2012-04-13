package me.exote.webauctionplus.listeners;

import java.math.BigDecimal;
import java.util.List;
import java.util.Random;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.AuctionPlayer;
import me.lorenzop.webauctionplus.dao.SaleAlert;

import org.bukkit.GameMode;
import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.block.Action;
import org.bukkit.event.player.PlayerInteractEvent;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.event.player.PlayerQuitEvent;

public class WebAuctionPlayerListener implements Listener {

	private final WebAuctionPlus plugin;

	public WebAuctionPlayerListener(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public static double round(double unrounded, int precision, int roundingMode) {
		BigDecimal bd = new BigDecimal(unrounded);
		BigDecimal rounded = bd.setScale(precision, roundingMode);
		return rounded.doubleValue();
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerQuit(PlayerQuitEvent event) {
		plugin.lastSignUse.remove(event.getPlayer().getName());
	}

	@EventHandler(priority = EventPriority.HIGHEST)
	public void onPlayerJoin(PlayerJoinEvent event) {
		Player p = event.getPlayer();
		String player = p.getName();

		// Alert player of new sale alerts
		if (plugin.showSalesOnJoin == true) {
			List<SaleAlert> saleAlerts = plugin.dataQueries.getNewSaleAlertsForSeller(player);
			for (SaleAlert saleAlert : saleAlerts) {
				p.sendMessage(plugin.chatPrefix + "You sold " +
					saleAlert.getQty() + " " +
					saleAlert.getItemName() + " to " +
					saleAlert.getBuyerName() + " for $" +
					saleAlert.getPriceEach() + " each, $" +
					saleAlert.getPriceTotal() + " total.");
			}
		}

		// Alert player of new mail
		int mailCount = plugin.dataQueries.hasMail(player);
		plugin.log.info(plugin.logPrefix + "Player " + player + " has " + Integer.toString(mailCount) + " items in mailbox.");
		if (mailCount > 0) {
			p.sendMessage(plugin.chatPrefix + "You have [ " + Integer.toString(mailCount) + " ] new items in your mail!");
		}

		// Determine permissions
		boolean canBuy  = plugin.permission.has(p, "wa.canbuy");
		boolean canSell = plugin.permission.has(p, "wa.cansell");
		boolean isAdmin = plugin.permission.has(p, "wa.webadmin");

		AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
		if (auctionPlayer != null) {
			plugin.log.info(plugin.logPrefix + "Player found - " + player +
				" with perms: canbuy=" + Boolean.toString(canBuy) + " cansell=" +
				Boolean.toString(canSell) + " isAdmin=" + Boolean.toString(isAdmin) );
			// Update permissions
			plugin.dataQueries.updatePlayerPermissions(player, auctionPlayer, canBuy, canSell, isAdmin);
		}
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerInteract(PlayerInteractEvent event) {
		if (event.getAction() != Action.RIGHT_CLICK_BLOCK) {
			return;
		}

		Block block = event.getClickedBlock();
		if (block == null || block.getType() != Material.SIGN_POST) {
			if (block.getType() != Material.WALL_SIGN) {
				return;
			}
		}

		// it's a sign
		Sign sign = (Sign) block.getState();
		String[] lines = sign.getLines();
		if (!lines[0].equals("[WebAuction+]")) {
			return;
		}

		Player p = event.getPlayer();
		String player = p.getName();
		event.setCancelled(true);

		// Make sure we can use the sign
		if (plugin.lastSignUse.containsKey(player)) {
			long lastSignUse = plugin.lastSignUse.get(player);
			if (lastSignUse + (long)plugin.signDelay > plugin.getCurrentMilli()) {
//				p.sendMessage(plugin.chatPrefix + "Please wait a bit before using that again");
				return;
			}
		}
		plugin.lastSignUse.put(player, plugin.getCurrentMilli());

		// Shout sign
		if (lines[1].equals("Shout")) {
			Random generator = new Random();
			int roll = generator.nextInt(20);
			switch (roll) {
			case 0:
				p.sendMessage(plugin.chatPrefix + "RAAN MIR TAH!");
				break;
			case 1:
				p.sendMessage(plugin.chatPrefix + "LAAS YAH NIR!");
				break;
			case 2:
				p.sendMessage(plugin.chatPrefix + "FEIM ZII GRON!");
				break;
			case 3:
				p.sendMessage(plugin.chatPrefix + "OD AH VIING!");
				break;
			case 4:
				p.sendMessage(plugin.chatPrefix + "HUN KAL ZOOR!");
				break;
			case 5:
				p.sendMessage(plugin.chatPrefix + "LOK VAH KOOR!");
				break;
			case 6:
				p.sendMessage(plugin.chatPrefix + "ZUN HAAL VIK!");
				break;
			case 7:
				p.sendMessage(plugin.chatPrefix + "FAAS RU MAAR!");
				break;
			case 8:
				p.sendMessage(plugin.chatPrefix + "JOOR ZAH FRUL!");
				break;
			case 9:
				p.sendMessage(plugin.chatPrefix + "SU GRAH DUN!");
				break;
			case 10:
				p.sendMessage(plugin.chatPrefix + "YOL TOOR SHOL!");
				break;
			case 11:
				p.sendMessage(plugin.chatPrefix + "FO KRAH DIIN!");
				break;
			case 12:
				p.sendMessage(plugin.chatPrefix + "LIZ SLEN NUS!");
				break;
			case 13:
				p.sendMessage(plugin.chatPrefix + "KAAN DREM OV!");
				break;
			case 14:
				p.sendMessage(plugin.chatPrefix + "KRII LUN AUS!");
				break;
			case 15:
				p.sendMessage(plugin.chatPrefix + "TIID KLO UL!");
				break;
			case 16:
				p.sendMessage(plugin.chatPrefix + "STRUN BAH QO!");
				break;
			case 17:
				p.sendMessage(plugin.chatPrefix + "ZUL MEY GUT!");
				break;
			case 18:
				p.sendMessage(plugin.chatPrefix + "WULK NAH KEST!");
				break;
			default:
				p.sendMessage(plugin.chatPrefix + "FUS RO DAH!");
				break;
			}

		// Deposit sign (money)
		} else if (lines[1].equals("Deposit")) {
			if (!plugin.permission.has(p, "wa.use.deposit.money")) {
				p.sendMessage(plugin.chatPrefix + "You do not have enough money in your pocket.");
			} else {
				double amount = 0.0D;
				if (!lines[2].equals("All")) {
					amount = Double.parseDouble(lines[2]);
				}
				if (plugin.economy.has(player, amount)) {
					AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
					if (null != auctionPlayer) {
						double currentMoney = auctionPlayer.getMoney();
						if (lines[2].equals("All")) {
							amount = plugin.economy.getBalance(player);
						}
						currentMoney += amount;
						currentMoney = round(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
						p.sendMessage(plugin.chatPrefix + "Added " + amount +
							" to auction account, new auction balance: " + currentMoney);
						plugin.dataQueries.updatePlayerMoney(player, currentMoney);
						plugin.economy.withdrawPlayer(player, amount);
					} else {
						p.sendMessage(plugin.chatPrefix + "No WebAuction account found, try logging off and back on again");
					}
				}
			}

		// Withdraw sign (money)
		} else if (lines[1].equals("Withdraw")) {
			if (!plugin.permission.has(p, "wa.use.withdraw.money")) {
				p.sendMessage(plugin.chatPrefix + "You do not have permission to withdraw money");
				event.setCancelled(true);
			} else {
				double amount = 0.0D;
				if (!lines[2].equals("All")) {
					amount = Double.parseDouble(lines[2]);
				} try {
					AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
					if (null == auctionPlayer) {
						p.sendMessage(plugin.chatPrefix + "No WebAuction account found, try logging off and back on again");
					} else {
						// Match found!
						double currentMoney = auctionPlayer.getMoney();
						if (lines[2].equals("All")) {
							amount = currentMoney;
						}
						if (currentMoney >= amount) {
							currentMoney -= amount;
							currentMoney = round(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
							p.sendMessage(plugin.chatPrefix + "Removed " +
								amount + " from auction account, new auction balance: " + currentMoney);
							plugin.dataQueries.updatePlayerMoney(player, currentMoney);
							plugin.economy.depositPlayer(player, amount);
						} else {
							p.sendMessage(plugin.chatPrefix + "You do not have enough money in your WebAuction account.");
						}
					}
				} catch (Exception e) {
					e.printStackTrace();
				}
			}

		// MailBox Deposit (items)
		} else if (lines[1].equals("MailBox") && lines[2].equals("Deposit")) {
			if (!plugin.permission.has(p, "wa.use.deposit.items")) {
				p.sendMessage(plugin.chatPrefix +
					"You do not have permission to use the mailbox");
				event.setCancelled(true);
				return;
			// disallow creative
			} else if (p.getGameMode() != GameMode.SURVIVAL) {
				p.sendMessage(plugin.chatPrefix + "Hey, no cheating!");
				event.setCancelled(true);
				return;
			}
			if (!plugin.waPlayerActions.DepositStack(p))
				event.setCancelled(true);
			return;

		// MailBox Withdraw (items)
		} else if (lines[1].equals("MailBox") && lines[2].equals("Withdraw")) {
			if (!plugin.permission.has(p, "wa.use.withdraw.items")) {
				p.sendMessage(plugin.chatPrefix + "You do not have permission to use the mailbox");
				event.setCancelled(true);
				return;
			}
			if (!plugin.waPlayerActions.WithdrawStacks(p))
				event.setCancelled(true);
			return;
		}
	}

}