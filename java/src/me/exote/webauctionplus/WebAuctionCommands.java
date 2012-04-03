package me.exote.webauctionplus;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

import org.bukkit.command.Command;
import org.bukkit.command.CommandExecutor;
import org.bukkit.command.CommandSender;
import org.bukkit.entity.Player;

public class WebAuctionCommands implements CommandExecutor {

	private final WebAuctionPlus plugin;

	public WebAuctionCommands(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public static String MD5(String str) {
		MessageDigest md = null;
		try {
			md = MessageDigest.getInstance("MD5");
		} catch (NoSuchAlgorithmException e) {
			e.printStackTrace();
		}
		md.update(str.getBytes());

		byte[] byteData = md.digest();

		StringBuffer hexString = new StringBuffer();
		for (int i = 0; i < byteData.length; i++) {
			String hex = Integer.toHexString(0xFF & byteData[i]);
			if (hex.length() == 1) {
				hexString.append('0');
			}
			hexString.append(hex);
		}
		return hexString.toString();
	}

	public boolean onCommand(CommandSender sender, Command command, String label, String[] args) {

		int params = args.length;

		String player = "";
		if (sender instanceof Player) {
			player = ((Player) sender).getName();
		}

		if (params == 0) {
			return false;
		} else if (params == 1) {
			// /wa reload
			if (args[0].equalsIgnoreCase("reload")){
				// from player
				if (sender instanceof Player) {
					if (!sender.hasPermission("wa.reload")){
						((Player)sender).sendMessage(plugin.chatPrefix + "You do not have permission");
						return false;
					}
					((Player)sender).sendMessage(plugin.chatPrefix + "reloading..");
				}
				plugin.log.info(plugin.logPrefix + "reloading..");
				plugin.onDisable();
				plugin.onEnable();
				return true;
			}
			return false;
		} else if (params == 2) {
			// /wa password
			if (args[0].equals("password")) {
				if (!(sender instanceof Player)) {
					plugin.log.info(plugin.logPrefix + "/wa password must be used by a player.");
					return false;
				}
				if (args[1] != null) {
					int canBuy = 0;
					int canSell = 0;
					int isAdmin = 0;
					if (plugin.permission.has(sender, "wa.canbuy")) {
						canBuy = 1;
					}
					if (plugin.permission.has(sender, "wa.cansell")) {
						canSell = 1;
					}
					if (plugin.permission.has(sender, "wa.webadmin")) {
						isAdmin = 1;
					}
					if (plugin.dataQueries.getPlayer(player) == null) {
						plugin.log.info(plugin.logPrefix + "Player not found, creating account");
						// create that person in database
						plugin.dataQueries.createPlayer(player, "Password", 0.0D, canBuy, canSell, isAdmin);
					}
					String newPass = MD5(args[1]);
					plugin.dataQueries.updatePlayerPassword(player, newPass);
					sender.sendMessage(plugin.chatPrefix + "Password changed");
					return true;
				}
			}
		}
		return false;
	}

}