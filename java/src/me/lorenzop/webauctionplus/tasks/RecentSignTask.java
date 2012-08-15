package me.lorenzop.webauctionplus.tasks;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.Map.Entry;

import me.lorenzop.webauctionplus.WebAuctionPlus;

import org.apache.commons.lang.StringUtils;
import org.bukkit.Bukkit;
import org.bukkit.Location;
import org.bukkit.Material;
import org.bukkit.block.Sign;

import com.bergerkiller.bukkit.sl.API.TickMode;
import com.bergerkiller.bukkit.sl.API.Variable;
import com.bergerkiller.bukkit.sl.API.Variables;

public class RecentSignTask implements Runnable {

	Variable varTitle;
	Variable varQtyPrice;
	Variable varSeller;
	Variable varType;

	private final WebAuctionPlus plugin;

	public RecentSignTask(WebAuctionPlus plugin) {
		this.plugin = plugin;
		// register signlink variables
		// %waTitle  - items name
		// %waPrice  - price each
		// %waSeller - sellers name
		// %waType   - Buy Now or Auction
		if(WebAuctionPlus.useSignLink()) {
			varTitle	= Variables.get("waTitle");
			varQtyPrice	= Variables.get("waPrice");
			varSeller	= Variables.get("waSeller");
			varType		= Variables.get("waType");
			varTitle.setDefault		("N/A       ");
			varQtyPrice.setDefault	("N/A       ");
			varSeller.setDefault	("N/A       ");
			varType.setDefault		("N/A       ");
			varTitle.getTicker().interval	= 10;
			varQtyPrice.getTicker().interval= 10;
			varSeller.getTicker().interval	= 10;
			varType.getTicker().interval	= 10;
			varTitle.getTicker().mode		= TickMode.LEFT;
			varQtyPrice.getTicker().mode	= TickMode.LEFT;
			varSeller.getTicker().mode		= TickMode.LEFT;
			varType.getTicker().mode		= TickMode.LEFT;
		}
	}


	public void run() {
		if(!WebAuctionPlus.useSignLink() && !WebAuctionPlus.useOriginalRecent()) return;
		if(Bukkit.getServer().getOnlinePlayers().length == 0) return;
		// signlink vars
		String tickTitle	= "";
		String tickQtyPrice	= "";
		String tickSeller	= "";
		String tickType		= "";
		// query auctions
		Connection conn = WebAuctionPlus.dataQueries.getConnection();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: RecentSignTask");
			st = conn.prepareStatement("SELECT `playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, `itemTitle`, "+
				"`price`, UNIX_TIMESTAMP(`created`) AS `created`, `allowBids`, `currentWinner` " +
				"FROM `"+WebAuctionPlus.dataQueries.dbPrefix()+"Auctions` ORDER BY `id` DESC LIMIT ?");
			st.setInt(1, plugin.numberOfRecentLink);
			rs = st.executeQuery();
			int offset = 0;
			while(rs.next()) {
				offset++;
				String strTitle		= rs.getString("itemTitle");
				if(strTitle==null || strTitle.isEmpty()) strTitle = "N/A       ";
				int    qty			= rs.getInt("qty");
				String strPrice		= WebAuctionPlus.FormatPrice(rs.getFloat("price"));
				String strQtyPrice	= Integer.toString(qty)+"x "+strPrice;
				String strSeller	= rs.getString("playerName");
				if(strSeller==null || strSeller.isEmpty()) strSeller = "N/A       ";
				String strType = "";
				if(rs.getInt("allowBids") == 0)	strType = "Buy Now";
				else							strType = "Auction";

				// recent signs
				if(WebAuctionPlus.useOriginalRecent()) {
					String[] lines = {
						strTitle,
						strQtyPrice,
//						strSeller
						strType
					};
					UpdateRecentSigns(offset, lines);
				}

				// sign link
				if(WebAuctionPlus.useSignLink()) {
					int size = strTitle.length();
					if(strQtyPrice.length() > size)	size = strQtyPrice.length();
					if(strSeller.length() > size)	size = strSeller.length();
					if(strType.length() > size)		size = strType.length();
					size += 10;
					tickTitle		+= padString(strTitle,		size);
					tickQtyPrice	+= padString(strQtyPrice,	size);
					tickSeller		+= padString(strSeller,		size);
					tickType		+= padString(strType,		size);
				}

			}
		} catch(SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix + "Unable to update signs!");
			e.printStackTrace();
		} finally {
			WebAuctionPlus.dataQueries.closeResources(conn, st, rs);
		}

		// set signlink vars
		if(WebAuctionPlus.useSignLink()) {
			try {
				varTitle.set	(tickTitle);
				varQtyPrice.set	(tickQtyPrice);
				varSeller.set	(tickSeller);
				varType.set		(tickType);
			} catch(Exception e) {
				e.printStackTrace();
			}
		}

	}


	private void UpdateRecentSigns(int offset, String[] lines) {
		List<Location> SignsToRemove = new ArrayList<Location>();
		try {
			for(Entry<Location, Integer> entry : plugin.recentSigns.entrySet()) {
				if(entry.getValue() != offset) continue;
				Location loc = entry.getKey();
				// sign exists?
				Material mat = loc.getBlock().getType();
				if(mat != Material.SIGN && mat != Material.WALL_SIGN) {
					SignsToRemove.add(loc);
					continue;
				}
				Sign sign = (Sign) loc.getBlock().getState();
				sign.setLine(1, lines[0]);
				sign.setLine(2, lines[1]);
				sign.setLine(3, lines[2]);
				sign.update();
			}
		} catch(Exception e) {
			e.printStackTrace();
		}
		try {
			if(SignsToRemove.size() > 0)
				// Remove any signs flagged for removal
				for(Location signLoc : SignsToRemove) {
					plugin.recentSigns.remove(signLoc);
					WebAuctionPlus.dataQueries.removeRecentSign(signLoc);
					WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Removed invalid sign at location: "+signLoc);
				}
		} catch(Exception e) {
			e.printStackTrace();
		}
	}


	// pad string with spaces for scrolling signlink signs
	private String padString(String text, int size) {
		if(text == null) return null;
		if(text.length() > size) return text.substring(0, size-1);
		if(text.length() < size) return text+StringUtils.repeat(" ", size-text.length());
		return text;
	}


}