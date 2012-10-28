package me.lorenzop.webauctionplus.mysql;

import java.sql.PreparedStatement;
import java.sql.SQLException;

import me.lorenzop.webauctionplus.WebAuctionPlus;

public class MySQLUpdate {


	// update database
	public static void doUpdate(String fromVersion) {
		// update potions  (< 1.1.6)
		if(WebAuctionPlus.compareVersions(fromVersion, "1.1.6").equals("<"))
			UpdatePotions1_1_6();
	}


	// update potions
	private static void UpdatePotions1_1_6() {
		WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Updating potions for Minecraft 1.3");
		int affected = 0;
		affected += UpdatePotion(8193, 16273); // regen 0:45
		affected += UpdatePotion(8194, 16274); // speed 3:00
		affected += UpdatePotion(8195, 16307); // fire resist 3:00
		affected += UpdatePotion(8196, 16276); // poison 0:45
		affected += UpdatePotion(8197, 32725); // healing
		affected += UpdatePotion(8200, 16312); // weakness 1:30
		affected += UpdatePotion(8201, 16281); // strength 3:00
		affected += UpdatePotion(8202, 16314); // slow 1:30
		affected += UpdatePotion(8204, 32732); // harming
		affected += UpdatePotion(8225, 16305); // regen 2 0:22
		affected += UpdatePotion(8226, 16306); // speed 2 1:30
		affected += UpdatePotion(8228, 16308); // poison 2 0:22
		affected += UpdatePotion(8229, 32757); // healing 2
		affected += UpdatePotion(8233, 16313); // strength 2 1:30
		affected += UpdatePotion(8236, 32764); // harming 2
		affected += UpdatePotion(8257, 16337); // regen 2:00
		affected += UpdatePotion(8258, 16338); // speed 8:00
		affected += UpdatePotion(8259, 16371); // fire resist 8:00
		affected += UpdatePotion(8260, 16340); // poison 2:00
		affected += UpdatePotion(8264, 16376); // weakness 4:00
		affected += UpdatePotion(8265, 16345); // strength 8:00
		affected += UpdatePotion(8266, 16378); // slow 4:00
		affected += UpdatePotion(16378, 32691); // fire resist splash 2:15
		affected += UpdatePotion(16385, 32657); // regen splash 0:33
		affected += UpdatePotion(16386, 32658); // speed splash 2:15
		affected += UpdatePotion(16388, 32660); // poison splash 0:33
		affected += UpdatePotion(16389, 32721); // healing splash
		affected += UpdatePotion(16392, 32696); // weakness splash 1:07
		affected += UpdatePotion(16393, 32665); // strength splash 2:15
		affected += UpdatePotion(16394, 32762); // slow splash 2:15
		affected += UpdatePotion(16396, 32724); // harming splash
		affected += UpdatePotion(16418, 32690); // speed splash 2 1:07
		affected += UpdatePotion(16420, 32692); // poison splash 2 0:16
		affected += UpdatePotion(16421, 32689); // healing splash 2
		affected += UpdatePotion(16425, 32697); // strength splash 2 1:07
		affected += UpdatePotion(16428, 32692); // harming splash 2
		affected += UpdatePotion(16449, 32721); // regen splash 1:30
		affected += UpdatePotion(16450, 32722); // speed splash 6:00
		affected += UpdatePotion(16451, 32755); // fire resist splash 6:00
		affected += UpdatePotion(16452, 32724); // poison splash 1:30
		affected += UpdatePotion(16456, 32760); // weakness splash 3:00
		affected += UpdatePotion(16457, 32729); // strength splash 6:00
		affected += UpdatePotion(16458, 32762); // slow splash 3:00
		affected += UpdatePotion(16471, 32689); // regen splash 2 0:16
		// guessing closest matching potion
		affected += UpdatePotion(16369, 32721); // regen splash 2 1:00
		affected += UpdatePotion(16370, 32722); // speed 2 4:00
		affected += UpdatePotion(16372, 32724); // poison 2 1:00
		affected += UpdatePotion(16377, 32729); // strength 2 4:00
		affected += UpdatePotion(32698, 32762); // slowness splash 1:07
		affected += UpdatePotion(32753, 32689); // regen spash 2 0:45
		affected += UpdatePotion(32754, 32722); // speed splash 2 3:00
		affected += UpdatePotion(32756, 32724); // poison splash 2 0:45
		affected += UpdatePotion(32761, 32729); // strength splash 2 3:00
		WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Updated "+Integer.toString(affected)+" potions");
	}
	private static int UpdatePotion(int fromId, int toId) {
		return
			UpdatePotion(fromId, toId, "Auctions") +
			UpdatePotion(fromId, toId, "Items");
	}
	private static int UpdatePotion(int fromId, int toId, String table) {
		MySQLPoolConn poolConn = WebAuctionPlus.dbPool.getLock();
		PreparedStatement st	= null;
		int affected = 0;
		try {
			st = poolConn.getConn().prepareStatement("UPDATE `"+poolConn.dbPrefix()+table+"` SET `itemDamage` = ? WHERE `itemId` = 373 AND `itemDamage` = ?");
			st.setInt(1, toId);
			st.setInt(2, fromId);
			affected = st.executeUpdate();
		} catch (SQLException e) {
			e.printStackTrace();
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to update potions!");
		} finally {
			poolConn.releaseLock(st);
			poolConn = null;
		}
		return affected;
	}


}