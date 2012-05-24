package me.lorenzop.webauctionplus.listeners;

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

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerQuit(PlayerQuitEvent event) {
		plugin.lastSignUse.remove(event.getPlayer().getName());
	}

	@EventHandler(priority = EventPriority.HIGHEST)
	public void onPlayerJoin(PlayerJoinEvent event) {
		Player p = event.getPlayer();
		String player = p.getName();
		AuctionPlayer waPlayer = plugin.dataQueries.getPlayer(player);
		if (waPlayer == null) return;

		// Update permissions
		boolean canBuy  = p.hasPermission("wa.canbuy");
		boolean canSell = p.hasPermission("wa.cansell");
		boolean isAdmin = p.hasPermission("wa.webadmin");
		plugin.dataQueries.updatePlayerPermissions(waPlayer, canBuy, canSell, isAdmin);
		WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Player found - " + player + " with perms: " +
				(canBuy ?"canBuy " :"") +
				(canSell?"canSell ":"") +
				(isAdmin?"isAdmin ":"") );

		// Alert player of new sale alerts
		if (plugin.showSalesOnJoin == true) {
			List<SaleAlert> saleAlerts = plugin.dataQueries.getNewSaleAlertsForSeller(player);
			for (SaleAlert saleAlert : saleAlerts) {
// TODO: language here
				p.sendMessage(WebAuctionPlus.chatPrefix + "You sold " +
					saleAlert.getQty() + " " +
					saleAlert.getItemName() + " to " +
					saleAlert.getBuyerName() + " for $" +
					saleAlert.getPriceEach() + " each, $" +
					saleAlert.getPriceTotal() + " total.");
			}
		}

		// Alert player of new mail
		int mailCount = plugin.dataQueries.hasMail(player);
// TODO: language here
		if (mailCount > 0) {
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Player " + player + " has " + Integer.toString(mailCount) + " items in mailbox.");
			p.sendMessage(WebAuctionPlus.chatPrefix + "You have [ " + Integer.toString(mailCount) + " ] new items in your mail!");
		}
	}

	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerInteract(PlayerInteractEvent event) {
		// right click only
		if (event.getAction() != Action.RIGHT_CLICK_BLOCK) return;
		Block block = event.getClickedBlock();
		// not a sign
		if (block == null || block.getType() != Material.SIGN_POST)
			if (block.getType() != Material.WALL_SIGN) return;
		// it's a sign
		Sign sign = (Sign) block.getState();
		String[] lines = sign.getLines();
		if (!lines[0].equals("[WebAuction+]")) return;

		Player p = event.getPlayer();
		String player = p.getName();
		event.setCancelled(true);

		// Make sure we can use the sign
		if (plugin.lastSignUse.containsKey(player))
			if( plugin.lastSignUse.get(player)+(long)plugin.signDelay > plugin.getCurrentMilli() ) return;
		//p.sendMessage(plugin.chatPrefix + "Please wait a bit before using that again");
		plugin.lastSignUse.put(player, plugin.getCurrentMilli());

		// Shout sign
		if (lines[1].equals("Shout")) {
			Random generator = new Random();
			int roll = generator.nextInt(20);
			switch (roll) {
			case 0:
				p.sendMessage(WebAuctionPlus.chatPrefix + "RAAN MIR TAH!");
				break;
			case 1:
				p.sendMessage(WebAuctionPlus.chatPrefix + "LAAS YAH NIR!");
				break;
			case 2:
				p.sendMessage(WebAuctionPlus.chatPrefix + "FEIM ZII GRON!");
				break;
			case 3:
				p.sendMessage(WebAuctionPlus.chatPrefix + "OD AH VIING!");
				break;
			case 4:
				p.sendMessage(WebAuctionPlus.chatPrefix + "HUN KAL ZOOR!");
				break;
			case 5:
				p.sendMessage(WebAuctionPlus.chatPrefix + "LOK VAH KOOR!");
				break;
			case 6:
				p.sendMessage(WebAuctionPlus.chatPrefix + "ZUN HAAL VIK!");
				break;
			case 7:
				p.sendMessage(WebAuctionPlus.chatPrefix + "FAAS RU MAAR!");
				break;
			case 8:
				p.sendMessage(WebAuctionPlus.chatPrefix + "JOOR ZAH FRUL!");
				break;
			case 9:
				p.sendMessage(WebAuctionPlus.chatPrefix + "SU GRAH DUN!");
				break;
			case 10:
				p.sendMessage(WebAuctionPlus.chatPrefix + "YOL TOOR SHOL!");
				break;
			case 11:
				p.sendMessage(WebAuctionPlus.chatPrefix + "FO KRAH DIIN!");
				break;
			case 12:
				p.sendMessage(WebAuctionPlus.chatPrefix + "LIZ SLEN NUS!");
				break;
			case 13:
				p.sendMessage(WebAuctionPlus.chatPrefix + "KAAN DREM OV!");
				break;
			case 14:
				p.sendMessage(WebAuctionPlus.chatPrefix + "KRII LUN AUS!");
				break;
			case 15:
				p.sendMessage(WebAuctionPlus.chatPrefix + "TIID KLO UL!");
				break;
			case 16:
				p.sendMessage(WebAuctionPlus.chatPrefix + "STRUN BAH QO!");
				break;
			case 17:
				p.sendMessage(WebAuctionPlus.chatPrefix + "ZUL MEY GUT!");
				break;
			case 18:
				p.sendMessage(WebAuctionPlus.chatPrefix + "WULK NAH KEST!");
				break;
			default:
				p.sendMessage(WebAuctionPlus.chatPrefix + "FUS RO DAH!");
				break;
			}
			return;

		// Deposit sign (money)
		} else if (lines[1].equals("Deposit")) {
			if (!p.hasPermission("wa.use.deposit.money")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				event.setCancelled(true);
				return;
			} else {
				double amount = 0.0D;
				if (!lines[2].equals("All")) {
					try {
						amount = WebAuctionPlus.ParseDouble(lines[2]);
					} catch(NumberFormatException ignore) {}
				}
				if (plugin.economy.has(player, amount)) {
					AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
					if (auctionPlayer != null) {
						double currentMoney = auctionPlayer.getMoney();
						if (lines[2].equals("All"))
							amount = plugin.economy.getBalance(player);
						currentMoney += amount;
						currentMoney = WebAuctionPlus.RoundDouble(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
						p.sendMessage(WebAuctionPlus.chatPrefix + "Added " + amount +
							" to auction account, new auction balance: " + currentMoney);
						plugin.dataQueries.updatePlayerMoney(player, currentMoney);
						plugin.economy.withdrawPlayer(player, amount);
					} else {
						p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("account_not_found"));
					}
				} else {
					p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("not_enough_money_pocket"));
				}
			}

		// Withdraw sign (money)
		} else if (lines[1].equals("Withdraw")) {
			if (!p.hasPermission("wa.use.withdraw.money")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				event.setCancelled(true);
				return;
			} else {
				double amount = 0.0D;
				try {
					AuctionPlayer auctionPlayer = plugin.dataQueries.getPlayer(player);
					if (auctionPlayer == null) {
						p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("account_not_found"));
					} else {
						// Match found!
						double currentMoney = auctionPlayer.getMoney();
						if (lines[2].equals("All"))
							amount = currentMoney;
						else {
							try {
								amount = WebAuctionPlus.ParseDouble(lines[2]);
							} catch(NumberFormatException ignore) {}
						}
						if (currentMoney >= amount) {
							currentMoney -= amount;
							currentMoney = WebAuctionPlus.RoundDouble(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
							p.sendMessage(WebAuctionPlus.chatPrefix + "Removed " +
								amount + " from auction account, new auction balance: " + currentMoney);
							plugin.dataQueries.updatePlayerMoney(player, currentMoney);
							plugin.economy.depositPlayer(player, amount);
						} else {
							p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("account_not_found"));
						}
					}
				} catch (Exception e) {
					e.printStackTrace();
				}
			}

		// MailBox Deposit (items)
		} else if (lines[1].equals("MailBox") && lines[2].equals("Deposit")) {
			if (!p.hasPermission("wa.use.deposit.items")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				event.setCancelled(true);
				return;
			// disallow creative
			} else if (p.getGameMode() != GameMode.SURVIVAL) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_cheating"));
				event.setCancelled(true);
				return;
			}
			if (!plugin.waPlayerActions.DepositStack(p))
				event.setCancelled(true);
			return;

		// MailBox Withdraw (items)
		} else if (lines[1].equals("MailBox") && lines[2].equals("Withdraw")) {
			if (!p.hasPermission("wa.use.withdraw.items")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				event.setCancelled(true);
				return;
			}
			int qty = 0;
			try {
				qty = Integer.parseInt(lines[3].replace("qty: ", ""));
			} catch(NumberFormatException ignore) {}
			if (!plugin.waPlayerActions.WithdrawStacks(p, qty))
				event.setCancelled(true);
			return;
		}
	}

}