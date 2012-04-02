package me.exote.webauction;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

import org.bukkit.command.Command;
import org.bukkit.command.CommandExecutor;
import org.bukkit.command.CommandSender;
import org.bukkit.entity.Player;

public class WebAuctionCommands implements CommandExecutor {

	private final WebAuction plugin;

	public WebAuctionCommands(WebAuction plugin) {
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

	@Override
	public boolean onCommand(CommandSender sender, Command command, String label, String[] split) {

		int params = split.length;
		String player = ((Player) sender).getName();
		if (params == 0) {
			return false;
		} else if (params == 1) {
			return false;
		} else if (params == 2) {
			if (split[0].equals("password")) {
				if (split[1] != null) {
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
					if (null != plugin.dataQueries.getPlayer(player)) {
						//no need to create a new account
					} else {
						plugin.log.info(plugin.logPrefix + "Player not found, creating account");
						// create that person in database
						plugin.dataQueries.createPlayer(player, "Password", 0.0d, canBuy, canSell, isAdmin);
					}
					String newPass = MD5(split[1]);
					plugin.dataQueries.updatePlayerPassword(player, newPass);
					sender.sendMessage(plugin.logPrefix + "Password changed");
					return true;
				}
			}
		}
		return false;
	}
}