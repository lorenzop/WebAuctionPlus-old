package me.lorenzop.webauctionplus;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;

import me.lorenzop.webauctionplus.mysql.DataQueries;
import me.lorenzop.webauctionplus.mysql.MySQLPoolConn;

import org.bukkit.Bukkit;
import org.bukkit.entity.Player;
import org.bukkit.inventory.Inventory;
import org.bukkit.inventory.ItemStack;

public class WebInventory {

	// inventory instances
	protected static HashMap<String, WebInventory> openInvs = new HashMap<String, WebInventory>();

	protected String playerName = null;
	protected Inventory chest = null;
	protected HashMap<Integer, Integer> tableRowIds = new HashMap<Integer, Integer>();
//	protected List<Integer> slotChanged = new ArrayList<Integer>();


	public WebInventory(Player p) {
		if(p == null) return;
		playerName = p.getName();
		int numSlots = WebAuctionPlus.MinMax( WebAuctionPlus.settings.getInteger("Inventory Rows"), 1, 6) * 9;
		String invTitle = WebAuctionPlus.Lang.getString("mailbox_title");
		if(invTitle == null || invTitle.isEmpty()) invTitle = "WebAuction+ MailBox";
		chest = Bukkit.getServer().createInventory(null, numSlots, invTitle);
		loadInventory();
		p.openInventory(chest);
	}


	// open mailbox
	public static void onInventoryOpen(Player p){
		if(p == null) return;
		String player = p.getName();
		synchronized(openInvs){
			// lock inventory
			setLocked(player, true);
			WebInventory inventory;
			if(openInvs.containsKey(player)) {
				// chest already open
				p.sendMessage(WebAuctionPlus.chatPrefix+"MailBox already opened!");
				WebAuctionPlus.log.warning("Inventory already open for "+player+"!");
				return;
//				inventory = openInvs.get(player);
//				p.openInventory(inventory.chest);
			} else {
				// create new virtual chest
				WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Inventory opened for: "+player);
				inventory = new WebInventory(p);
				openInvs.put(player, inventory);
			}
		}
		p.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("mailbox_opened"));
	}
	// close mailbox
	public static void onInventoryClose(Player p){
		if(p == null) return;
		String player = p.getName();
		synchronized(openInvs){
			if(!openInvs.containsKey(player)) return;
			WebInventory inventory = openInvs.get(player);
			// save inventory
			inventory.saveInventory();
			// remove inventory chest
			openInvs.remove(player);
			// unlock inventory
			setLocked(player, false);
		}
		WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"MailBox inventory closed and saved");
		p.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("mailbox_closed"));
	}
	public static void ForceCloseAll() {
		if(openInvs==null || openInvs.size()==0) return;
		for(String player : openInvs.keySet()) {
			Player p = Bukkit.getServer().getPlayerExact(player);
			p.closeInventory();
			WebInventory.onInventoryClose(p);
		}
	}


//	// inventory click
//	public static void onInventoryClick(Player p, int slot) {
//		if(p == null) return;
//		String player = p.getName();
//		if(!openInvs.containsKey(player)) return;
//		openInvs.get(player).onClick(slot);
//	}
//	protected void onClick(int slot) {
//		if(slot > chest.getSize()) return;
//		if(slotChanged.contains(slot)) return;
//WebAuctionPlus.log.warning("SLOT "+Integer.toString(slot));
//		slotChanged.add(slot);
//	}


	// inventory lock
	public static boolean isLocked(String player) {
		boolean locked = false;
		MySQLPoolConn poolConn = WebAuctionPlus.dbPool.getLock();
		PreparedStatement st = null;
		ResultSet rs = null;
		try {
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: isLocked");
			st = poolConn.getConn().prepareStatement("SELECT `Locked` FROM `"+poolConn.dbPrefix()+"Players` "+
				"WHERE `playerName` = ? LIMIT 1");
			st.setString(1, player);
			rs = st.executeQuery();
			// got lock state
			if(rs.next()) locked = (rs.getInt("Locked") != 0);
		} catch(SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to get inventory lock");
			e.printStackTrace();
			return true;
		} finally {
			poolConn.releaseLock(st, rs);
			poolConn = null;
		}
		return locked;
	}
	// set inventory lock
	public static void setLocked(String player, boolean locked) {
		MySQLPoolConn poolConn = WebAuctionPlus.dbPool.getLock();
		PreparedStatement st = null;
		try {
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: setLocked "+(locked?"engaged":"released"));
			st = poolConn.getConn().prepareStatement("UPDATE `"+poolConn.dbPrefix()+"Players` "+
				"SET `Locked` = ? WHERE `playerName` = ? LIMIT 1");
			if(locked) st.setInt   (1, 1);
			else st.setInt   (1, 0);
			st.setString(2, player);
			st.executeUpdate();
		} catch(SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to set inventory lock");
			e.printStackTrace();
		} finally {
			poolConn.releaseLock(st);
			poolConn = null;
		}
	}


	// load inventory from db
	protected void loadInventory() {
		MySQLPoolConn poolConn = WebAuctionPlus.dbPool.getLock();
		PreparedStatement st = null;
		ResultSet rs = null;
//		slotChanged.clear();
		chest.clear();
		tableRowIds.clear();
		try {
			if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: isLocked");
			st = poolConn.getConn().prepareStatement("SELECT `id`, `itemId`, `itemDamage`, `qty`, `enchantments`, `itemTitle` "+
				"FROM `"+poolConn.dbPrefix()+"Items` WHERE `playerName` = ? ORDER BY `id` ASC LIMIT ?");
			st.setString(1, playerName);
			st.setInt   (2, chest.getSize());
			rs = st.executeQuery();
			ItemStack[] stacks = new ItemStack[chest.getSize()];
			int i = -1;
			while(rs.next()) {
				if(rs.getInt("qty") < 1) continue;
				i++; if(i >= chest.getSize()) break;
				tableRowIds.put(i, rs.getInt("id"));
				// create/split item stack
				stacks[i] = getSplitItemStack( rs.getInt("id"), rs.getInt("itemId"), rs.getShort("itemDamage"), rs.getInt("qty"), rs.getString("enchantments"), rs.getString("itemTitle") );
				if(stacks[i] == null) tableRowIds.remove(i);
			}
			chest.setContents(stacks);
		} catch(SQLException e) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to set inventory lock");
			e.printStackTrace();
		} finally {
			poolConn.releaseLock(st, rs);
			poolConn = null;
		}
	}
	// create/split item stack
	private ItemStack getSplitItemStack(int itemRowId, int itemId, short itemDamage, int qty, String enchStr, String itemTitle) {
		ItemStack stack = new ItemStack(itemId, qty, itemDamage);
		int maxSize = stack.getMaxStackSize();
		if(maxSize < 1) return null;
		// split stack
		if(qty > maxSize) {
			MySQLPoolConn poolConn = WebAuctionPlus.dbPool.getLock();
			PreparedStatement st = null;
			while(qty > maxSize) {
				try {
					if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: getSplitItemStack  qty:"+Integer.toString(qty)+"  max:"+Integer.toString(maxSize));
					st = poolConn.getConn().prepareStatement("INSERT INTO `"+poolConn.dbPrefix()+"Items` ( "+
						"`playerName`, `itemId`, `itemDamage`, `qty`, `enchantments`, `itemTitle` )VALUES( ?, ?, ?, ?, ?, ? )");
					st.setString(1, playerName);
					st.setInt   (2, itemId);
					st.setShort (3, itemDamage);
					st.setInt   (4, maxSize);
					st.setString(5, enchStr);
					st.setString(6, itemTitle);
					st.executeUpdate();
				} catch(SQLException e) {
					WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to insert new item to inventory!");
					e.printStackTrace();
					return null;
				} finally {
					poolConn.freeResource(st);
				}
				qty -= maxSize;
			}
			stack.setAmount(qty);
			poolConn.releaseLock();
			poolConn = null;
		}
		// add enchantments
		if(enchStr != null && !enchStr.isEmpty())
			DataQueries.decodeEnchantments(Bukkit.getPlayer(playerName), stack, enchStr);
		return stack;
	}
	// save inventory to db
	protected void saveInventory() {
		MySQLPoolConn poolConn = WebAuctionPlus.dbPool.getLock();
		PreparedStatement st = null;
		int countInserted = 0;
		int countUpdated  = 0;
		int countDeleted  = 0;
		for(int i = 0; i < chest.getSize(); i++) {
//			if(!slotChanged.contains(i)) continue;
			ItemStack Item = chest.getItem(i);

			// empty slot
			if(Item == null || Item.getTypeId() == 0) {

				// delete item
				if(tableRowIds.containsKey(i)) {
					try {
						if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: saveInventory::delete slot "+Integer.toString(i));
						st = poolConn.getConn().prepareStatement("DELETE FROM `"+poolConn.dbPrefix()+"Items` WHERE `id` = ? LIMIT 1");
						st.setInt(1, tableRowIds.get(i));
						st.executeUpdate();
					} catch(SQLException e) {
						WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to delete item from inventory!");
						e.printStackTrace();
					} finally {
						poolConn.freeResource(st);
					}
					countDeleted++;
					continue;

				// no item
				} else {
					continue;
				}

			// item in slot
			} else {

				// update existing item
				if(tableRowIds.containsKey(i)) {
					try {
						if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: saveInventory::update slot "+Integer.toString(i));
						st = poolConn.getConn().prepareStatement("UPDATE `"+poolConn.dbPrefix()+"Items` SET "+
							"`itemId` = ?, `itemDamage` = ?, `qty` = ?, `enchantments` = ? WHERE `id` = ? LIMIT 1");
						st.setInt   (1, Item.getTypeId());
						st.setShort (2, Item.getDurability());
						st.setInt   (3, Item.getAmount());
						st.setString(4, DataQueries.encodeEnchantments(Bukkit.getPlayer(playerName), Item));
						st.setInt   (5, tableRowIds.get(i));
						st.executeUpdate();
					} catch(SQLException e) {
						WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to update item to inventory!");
						e.printStackTrace();
					} finally {
						poolConn.freeResource(st);
					}
					countUpdated++;
					continue;

				// insert new item
				} else {
					try {
						if(WebAuctionPlus.isDebug()) WebAuctionPlus.log.info("WA Query: saveInventory::insert slot "+Integer.toString(i));
						st = poolConn.getConn().prepareStatement("INSERT INTO `"+poolConn.dbPrefix()+"Items` ( "+
							"`playerName`, `itemId`, `itemDamage`, `qty`, `enchantments` )VALUES( ?, ?, ?, ?, ? )");
						st.setString(1, playerName);
						st.setInt   (2, Item.getTypeId());
						st.setShort (3, Item.getDurability());
						st.setInt   (4, Item.getAmount());
						st.setString(5, DataQueries.encodeEnchantments(Bukkit.getPlayer(playerName), Item));
						st.executeUpdate();
					} catch(SQLException e) {
						WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Unable to insert new item to inventory!");
						e.printStackTrace();
					} finally {
						poolConn.freeResource(st);
					}
					countInserted++;
					continue;

				}
			}

		}
		poolConn.releaseLock();
		poolConn = null;
//		slotChanged.clear();
		chest.clear();
		tableRowIds.clear();
		WebAuctionPlus.log.info(WebAuctionPlus.logPrefix+"Updated player inventory for: "+playerName+" ["+
			" Inserted:"+Integer.toString(countInserted)+
			" Updated:"+Integer.toString(countUpdated)+
			" Deleted:"+Integer.toString(countDeleted)+
			" ]");
	}


}