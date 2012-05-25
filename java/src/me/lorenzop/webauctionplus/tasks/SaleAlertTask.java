package me.lorenzop.webauctionplus.tasks;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;

import me.lorenzop.webauctionplus.WebAuctionPlus;
import me.lorenzop.webauctionplus.dao.AuctionPlayer;

import org.bukkit.Bukkit;
import org.bukkit.entity.Player;

public class SaleAlertTask implements Runnable {

	private String playerJoined = null;

	private WebAuctionPlus plugin;

	public SaleAlertTask(WebAuctionPlus plugin, String playerJoined) {
		this.plugin = plugin;
		this.playerJoined = playerJoined;
	}

	public synchronized void run() {
		HashMap<Integer, String> playersMap = new HashMap<Integer, String>();
		String whereSql = "";
		int i = 0;
		// build players online hashmap
		if(playerJoined == null) {
			Player[] playersList = plugin.getServer().getOnlinePlayers();
			// no players online
			if (playersList.length == 0) return;
			// build query
			for (Player p : playersList) {
				i++; if(i != 1) whereSql += " OR ";
				whereSql += "`seller` = ?";
				playersMap.put(i, p.getName());
			}
		// only running for a single joined player
		} else {
			AuctionPlayer waPlayer = plugin.dataQueries.getPlayer(playerJoined);
			// player doesn't have a web auction account
			if (waPlayer == null) return;
			// update permissions
			boolean canBuy  = waPlayer.getPlayer().hasPermission("wa.canbuy");
			boolean canSell = waPlayer.getPlayer().hasPermission("wa.cansell");
			boolean isAdmin = waPlayer.getPlayer().hasPermission("wa.webadmin");
			WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Player found - " + playerJoined + " with perms:" +
					(canBuy ?" canBuy" :"") +
					(canSell?" canSell":"") +
					(isAdmin?" isAdmin":"") );
			plugin.dataQueries.updatePlayerPermissions(waPlayer, canBuy, canSell, isAdmin);
			// build query
			whereSql += "seller = ?";
			playersMap.put(1, playerJoined);
		}
		if(playersMap.size() == 0) return;
		// run the querys
		String markSeenSql = "";
		Connection conn = plugin.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: SaleAlertTask::SaleAlerts " + playersMap.toString());
			st = conn.prepareStatement("SELECT `id`,`seller`,`qty`,`price`,`buyer`,`item` FROM `" +
				plugin.dataQueries.dbPrefix+"SaleAlerts` WHERE ( " + whereSql + " ) AND `alerted` = 0");
			for(Map.Entry<Integer, String> entry : playersMap.entrySet()) {
				st.setString(entry.getKey(), entry.getValue());
			}
			rs = st.executeQuery();
			while (rs.next()) {
//saleAlert.setAlertId(rs.getInt("id"));
//String formattedPrice = plugin.economy.format(saleAlert.getPriceEach());
				Player p = Bukkit.getPlayerExact(rs.getString("seller"));
				if(p != null) {
// TODO: language here
					p.sendMessage(WebAuctionPlus.chatPrefix+"You sold " +
						rs.getInt   ("qty") + " " +
						rs.getString("item") + " to " +
						rs.getString("buyer") + " for " +
						WebAuctionPlus.FormatPrice(rs.getDouble("price")) + " each, " +
						WebAuctionPlus.FormatPrice(rs.getDouble("price") * rs.getDouble("qty")) + " total.");
					// mark seen sql
					if(!markSeenSql.isEmpty()) markSeenSql += " OR ";
					markSeenSql += "`id` = " + Integer.toString(rs.getInt("id"));
				}
			}
			// mark seen
			if(!markSeenSql.isEmpty()) {
				if (plugin.dataQueries.debugSQL) WebAuctionPlus.log.info("WA Query: SaleAlertTask::SaleAlerts " + playersMap.toString());
				st = conn.prepareStatement("UPDATE `"+plugin.dataQueries.dbPrefix+"SaleAlerts` SET `alerted` = 1 WHERE " + markSeenSql);
				if(st.executeUpdate() == 0)
					WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Failed to mark sale alerts seen!");
			}
			// alert player of new mail
			if(playerJoined != null) {
				Player p = Bukkit.getPlayerExact(playerJoined);
				if(p != null) {
					int mailCount = plugin.dataQueries.hasMail(playerJoined);
					if (mailCount > 0) {
// TODO: language here
						WebAuctionPlus.log.info(WebAuctionPlus.logPrefix + "Player " + playerJoined + " has " + Integer.toString(mailCount) + " items in their mailbox.");
						p.sendMessage(WebAuctionPlus.chatPrefix + "You have [ " + Integer.toString(mailCount) + " ] items in your mail!");
					}
				}
			}
		} catch (SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to get sale alerts for players");
			e.printStackTrace();
		} finally {
			plugin.dataQueries.closeResources(conn, st, rs);
		}
	}

}