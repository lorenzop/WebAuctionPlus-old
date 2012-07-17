package me.lorenzop.webauctionplus.listeners;

import java.math.BigDecimal;

import me.lorenzop.webauctionplus.PlayerActions;
import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.AuctionPlayer;
import me.lorenzop.webauctionplus.tasks.PlayerAlertTask;

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
		String player = event.getPlayer().getName();
		// login code runs multi-threaded with a delay
		if (player != null)
			// run after 2 seconds
			plugin.getServer().getScheduler().scheduleAsyncDelayedTask(plugin, new PlayerAlertTask(player), 2 * 20);
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
		// get player info
		Player p = event.getPlayer();
		String player = p.getName();
		event.setCancelled(true);
		// prevent click spamming signs
		if (plugin.lastSignUse.containsKey(player))
			if( plugin.lastSignUse.get(player)+(long)plugin.signDelay > WebAuctionPlus.getCurrentMilli() ) return;
		//p.sendMessage(plugin.chatPrefix + "Please wait a bit before using that again");
		plugin.lastSignUse.put(player, WebAuctionPlus.getCurrentMilli());

		// Shout sign
		if(lines[1].equals("Shout")) {
			PlayerActions.clickSignShout();
			return;
		}

		// Deposit sign (money)
		if(lines[1].equals("Deposit")) {
			if(!p.hasPermission("wa.use.deposit.money")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				return;
			}
			double amount = 0.0D;
			if(!lines[2].equals("All")) {
				try {
					amount = WebAuctionPlus.ParseDouble(lines[2]);
				} catch(NumberFormatException ignore) {}
			}
			// player has enough money
			if(!plugin.economy.has(player, amount)) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("not_enough_money_pocket"));
				return;
			}
			AuctionPlayer auctionPlayer = WebAuctionPlus.dataQueries.getPlayer(player);
			if(auctionPlayer == null) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("account_not_found"));
				return;
			}
			double currentMoney = auctionPlayer.getMoney();
			if(lines[2].equals("All"))
				amount = plugin.economy.getBalance(player);
			currentMoney += amount;
			currentMoney = WebAuctionPlus.RoundDouble(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
			p.sendMessage(WebAuctionPlus.chatPrefix + "Added " + amount +
				" to auction account, new auction balance: " + currentMoney);
			WebAuctionPlus.dataQueries.updatePlayerMoney(player, currentMoney);
			plugin.economy.withdrawPlayer(player, amount);
			return;
		}

		// Withdraw sign (money)
		if(lines[1].equals("Withdraw")) {
			if(!p.hasPermission("wa.use.withdraw.money")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				return;
			}
			double amount = 0.0D;
			try {
				AuctionPlayer auctionPlayer = WebAuctionPlus.dataQueries.getPlayer(player);
				if(auctionPlayer == null) {
					p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("account_not_found"));
					return;
				}
				// Match found!
				double currentMoney = auctionPlayer.getMoney();
				if(lines[2].equals("All")) {
					amount = currentMoney;
				} else {
					try {
						amount = WebAuctionPlus.ParseDouble(lines[2]);
					} catch(NumberFormatException ignore) {}
				}
				if(currentMoney < amount) {
					p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("not_enough_money_account"));
					return;
				}
				currentMoney -= amount;
				currentMoney = WebAuctionPlus.RoundDouble(currentMoney, 2, BigDecimal.ROUND_HALF_UP);
				p.sendMessage(WebAuctionPlus.chatPrefix + "Removed " +
					amount + " from auction account, new auction balance: " + currentMoney);
				WebAuctionPlus.dataQueries.updatePlayerMoney(player, currentMoney);
				plugin.economy.depositPlayer(player, amount);
			} catch (Exception e) {
				e.printStackTrace();
			}
			return;
		}

		// MailBox Deposit (items)
		if(lines[1].equals("MailBox") && lines[2].equals("Deposit")) {
			if(!p.hasPermission("wa.use.deposit.items")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				return;
			}
			// disallow creative
			if(p.getGameMode() != GameMode.SURVIVAL) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_cheating"));
				return;
			}
			PlayerActions.DepositStack(p);
			return;
		}

		// MailBox Withdraw (items)
		if(lines[1].equals("MailBox") && lines[2].equals("Withdraw")) {
			if(!p.hasPermission("wa.use.withdraw.items")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				return;
			}
			int qty = 0;
			try {
				qty = Integer.parseInt(lines[3].replace("qty: ", ""));
			} catch(NumberFormatException ignore) {}
			PlayerActions.WithdrawStacks(p, qty);
			return;
		}

	}

}