package me.lorenzop.webauctionplus.listeners;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.AuctionPlayer;

import org.bukkit.Bukkit;
import org.bukkit.command.Command;
import org.bukkit.command.CommandExecutor;
import org.bukkit.command.CommandSender;
import org.bukkit.entity.Player;

public class WebAuctionCommands implements CommandExecutor {

	private final WebAuctionPlus plugin;

	public WebAuctionCommands(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public boolean onCommand(CommandSender sender, Command command, String label, String[] args) {
		int params = args.length;
		String player = "";
		if(sender instanceof Player) player = ((Player) sender).getName();
		// 0 args
		if(params == 0) {
			return false;
		}
		// 1 arg
		if(params == 1) {
			// wa reload
			if(args[0].equalsIgnoreCase("reload")){
				if(sender instanceof Player) {
					if(!sender.hasPermission("wa.reload")){
						((Player)sender).sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("no_permission"));
						return true;
					}
				}
				if(sender instanceof Player)
					sender.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("reloading"));
				WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+WebAuctionPlus.Lang.getString("reloading"));
				plugin.onReload();
				if(WebAuctionPlus.isOk()) {
					if(sender instanceof Player)
						sender.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("finished_reloading"));
					WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+WebAuctionPlus.Lang.getString("finished_reloading"));
				} else {
					if(sender instanceof Player)
						sender.sendMessage(WebAuctionPlus.chatPrefix+"Failed to reload!");
					WebAuctionPlus.log.severe(WebAuctionPlus.logPrefix+"Failed to reload!");
				}
				return true;
			}
			// wa version
			if (args[0].equalsIgnoreCase("version")) {
				if(sender instanceof Player) {
					sender.sendMessage(WebAuctionPlus.chatPrefix+"v"+plugin.getDescription().getVersion());
					if(WebAuctionPlus.newVersionAvailable && sender.hasPermission("wa.webadmin"))
						sender.sendMessage(WebAuctionPlus.chatPrefix+"A new version is available! " + WebAuctionPlus.newVersion);
				} else {
					WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"v"+plugin.getDescription().getVersion());
					if(WebAuctionPlus.newVersionAvailable) {
						WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"A new version is available! " + WebAuctionPlus.newVersion);
						WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"http://dev.bukkit.org/server-mods/webauctionplus");
					}
				}
				return true;
			}
			// wa update
			if(args[0].equalsIgnoreCase("update")){
				if(!sender.hasPermission("wa.reload")){
					sender.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("no_permission"));
					return true;
				}
				WebAuctionPlus.recentSignTask.run();
				if(sender instanceof Player)
					sender.sendMessage(WebAuctionPlus.chatPrefix+"Updated recent signs.");
				WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Updated recent signs.");
				return true;
			}
			return false;
		}
		if(!WebAuctionPlus.isOk()) {sender.sendMessage(WebAuctionPlus.chatPrefix+"Plugin isn't loaded"); return true;}
		// 2 args
		if(params == 2 || params == 3) {
			// wa password
			if (args[0].equalsIgnoreCase("password") ||
				args[0].equalsIgnoreCase("pass")     ||
				args[0].equalsIgnoreCase("pw")       ) {
				String pass = "";
				// is player
				boolean isPlayer = (sender instanceof Player);
				if (isPlayer) {
					if (params != 2 || args[1].isEmpty()) return false;
					pass = WebAuctionPlus.MD5(args[1]);
					args[1] = "";
				// is console
				} else {
					if (params != 3) return false;
					if (args[1].isEmpty() || args[2].isEmpty()) return false;
					player = args[1];
					if(!Bukkit.getOfflinePlayer(player).hasPlayedBefore()) {
						sender.sendMessage(WebAuctionPlus.logPrefix+"Player not found!");
						sender.sendMessage(WebAuctionPlus.logPrefix+"Note: if you really need to, you can add a player to the database, just md5 the password.");
						return true;
					}
					pass = WebAuctionPlus.MD5(args[2]);
					args[2] = "";
				}
				if(player.isEmpty()) return false;
				AuctionPlayer waPlayer = WebAuctionPlus.dataQueries.getPlayer(player);
				// create that person in database
				if(waPlayer == null) {
					// permission to create an account
					if (isPlayer) {
						if (!sender.hasPermission("wa.password.create")){
							((Player)sender).sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
							return true;
						}
					}
					waPlayer = new AuctionPlayer(player);
					waPlayer.setPerms(
						sender.hasPermission("wa.canbuy")   && isPlayer,
						sender.hasPermission("wa.cansell")  && isPlayer,
						sender.hasPermission("wa.webadmin") && isPlayer
					);
					WebAuctionPlus.dataQueries.createPlayer(waPlayer, pass);
					if (sender instanceof Player)
						sender.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("account_created"));
					WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + WebAuctionPlus.Lang.getString("account_created") + " " + player +
							" with perms: " + waPlayer.getPermsString());
				// change password for an existing account
				} else {
					// permission to change password
					if(sender instanceof Player) {
						if (!sender.hasPermission("wa.password.change")){
							((Player)sender).sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_permission"));
							return true;
						}
					}
					WebAuctionPlus.dataQueries.updatePlayerPassword(player, pass);
					if(sender instanceof Player)
						sender.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("password_changed"));
					WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + WebAuctionPlus.Lang.getString("password_changed") + " " + player);
				}
				return true;
			}
			return false;
		}
		// 4 args
		if(params == 4) {
//			// wa give <player> <item> <count>
//			if (args[0].equals("give")) {
// /wa give lorenzop diamond 3
//			}
			return false;
		}
		return false;
	}

}