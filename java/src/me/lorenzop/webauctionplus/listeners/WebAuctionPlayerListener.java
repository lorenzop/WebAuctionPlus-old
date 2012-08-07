package me.lorenzop.webauctionplus.listeners;

import java.math.BigDecimal;
import java.util.Random;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.WebInventory;
import me.lorenzop.webauctionplus.dao.AuctionPlayer;
import me.lorenzop.webauctionplus.tasks.PlayerAlertTask;

import org.bukkit.Bukkit;
import org.bukkit.GameMode;
import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.block.Sign;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.EventPriority;
import org.bukkit.event.Listener;
import org.bukkit.event.block.Action;
import org.bukkit.event.inventory.InventoryCloseEvent;
import org.bukkit.event.player.PlayerInteractEvent;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.event.player.PlayerQuitEvent;

public class WebAuctionPlayerListener implements Listener {

	private final WebAuctionPlus plugin;

	public WebAuctionPlayerListener(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}


	// join
	@EventHandler(priority = EventPriority.HIGHEST)
	public void onPlayerJoin(PlayerJoinEvent event) {
		String player = event.getPlayer().getName();
		if (player == null) return;
		// login code runs multi-threaded with a delay
		// run after 2 seconds
		plugin.getServer().getScheduler().scheduleAsyncDelayedTask(plugin, new PlayerAlertTask(player), 2 * 20);
	}
	// quit
	@EventHandler(priority = EventPriority.NORMAL)
	public void onPlayerQuit(PlayerQuitEvent event) {
		plugin.lastSignUse.remove(event.getPlayer().getName());
	}


	// close inventory
	@EventHandler(priority = EventPriority.NORMAL)
	public void onInventoryClose(InventoryCloseEvent event){
		WebInventory.onInventoryClose( (Player) event.getPlayer() );
//		if(WebAuctionPlus.dataQueries.debugSQL) WebAuctionPlus.log.info(WebAuctionPlus.dataQueries."CLOSED!");
//		Bukkit.getServer().broadcastMessage("Close Inventory");
	}


//	// inventory click
//	@EventHandler(priority = EventPriority.NORMAL)
//	public void onInventoryClickEvent(InventoryClickEvent event) {
//		if(event == null) return;
//		// not an inventory click
//		if(event.getCurrentItem() == null) return;
//		if(!(event.getWhoClicked() instanceof Player)) return;
//		WebInventory.onInventoryClick( (Player) event.getWhoClicked(), event.getRawSlot() );
//	}


	@EventHandler(priority = EventPriority.LOWEST, ignoreCancelled = true)
	public void onPlayerInteract(PlayerInteractEvent event) {
		// right click only
		if (event.getAction() != Action.RIGHT_CLICK_BLOCK &&
			event.getAction() != Action.RIGHT_CLICK_AIR) return;
		Block block = event.getClickedBlock();
		// not a sign
		if(block == null) return;
		if(block.getType() != Material.SIGN_POST && block.getType() != Material.WALL_SIGN) return;
		// it's a sign
		Sign sign = (Sign) block.getState();
		String[] lines = sign.getLines();
		if (!lines[0].equals("[WebAuction+]")) return;
		event.setCancelled(true);
		// get player info
		Player p = event.getPlayer();
		String player = p.getName();

		// prevent click spamming signs
		if (plugin.lastSignUse.containsKey(player))
			if( plugin.lastSignUse.get(player)+(long)plugin.signDelay > WebAuctionPlus.getCurrentMilli() ) {
				p.sendMessage(WebAuctionPlus.chatPrefix + "Please wait a bit before using that again");
				return;
			}
		plugin.lastSignUse.put(player, WebAuctionPlus.getCurrentMilli());

		// Shout sign
		if(lines[1].equals("Shout")) {
			clickSignShout();
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

		// Mailbox (items)
		if(lines[1].equals("MailBox")) {
			if(!p.hasPermission("wa.use.mailbox")) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
				return;
			}
			// disallow creative
			if(p.getGameMode() != GameMode.SURVIVAL) {
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_cheating"));
				return;
			}
			// load virtual chest
			WebInventory.onInventoryOpen(p);
			return;
		}

	}


	// shout sign
	private static long lastUseShout = 0;
	public static void clickSignShout() {
		if(lastUseShout+(10*60*1000) > WebAuctionPlus.getCurrentMilli()) return;
		lastUseShout = WebAuctionPlus.getCurrentMilli();
		Random generator = new Random();
		while(true) {
			int roll = generator.nextInt(11);
			switch(roll) {
				case 0: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"All your base are belong to Notch!"); return;
				case 1: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"Mmmmm, chocolate milk."); return;
				case 2: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"I like potatos."); return;
				case 3: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"99% mime free!"); return;
				case 4: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"BAGOCK! I sorry, I thought you was corn."); return;
				case 5: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"Hey, there's a creeper behind you! jk"); return;
				case 6: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"It's a trap!"); return;
				case 7: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"Who's Mary Ann?!"); return;
				case 8: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"It's like forgetting the words to your favorite song."); return;
				case 9: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"Vote for net neutrality!"); return;
				case 10:Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"That creeper stole your wallet!"); return;
			}
		}
	}


}