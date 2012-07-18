package me.lorenzop.webauctionplus;

import java.util.Random;

import me.lorenzop.webauctionplus.mysql.DataQueries;

import org.bukkit.Bukkit;
import org.bukkit.ChatColor;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;

public class PlayerActions {


	// deposit items
	public static synchronized boolean DepositStack(Player p) {
		if(p == null) return false;
		String player = p.getName();
		// get item/stack in hand
		ItemStack stack = p.getItemInHand();
		if(stack == null) return false;
		// no item in hand
		if(stack.getTypeId() == 0) {
			p.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("no_item_in_hand"));
			return false;
		}
		boolean depositOk = false;
		// has existing stack
		int keyId = WebAuctionPlus.dataQueries.getItemStackId(player, stack);
		if(keyId > 0) {
			// add to existing stack
			depositOk = WebAuctionPlus.dataQueries.AddItemQty(keyId, stack.getAmount());
		} else {
			// add new item stack
			depositOk = (WebAuctionPlus.dataQueries.CreateItem(player, stack) > 0);
		}
		if(depositOk) {
			p.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("item_stack_stored"));
			p.setItemInHand(null);
			return true;
		} else
			p.sendMessage(WebAuctionPlus.chatPrefix+"Failed to deposit item!");
		return false;
	}


	// withdraw items
	public static boolean WithdrawStacks(Player p) {
		return WithdrawStacks(p, 0);
	}
	public static synchronized boolean WithdrawStacks(Player p, int qty) {
		if(p == null) return false;
		DataQueries.MailGetter mailGetter = new DataQueries.MailGetter();
		String player = p.getName();
		if(qty<1 || qty>36) qty = 36;
//		HashMap<Integer, Integer> delMail = new HashMap<Integer, Integer>();
		int gotMail = 0;
		boolean invFull = false;
		try {
			// get items from mailbox
			for (int i=0; i<qty; i++) {
				int firstEmpty = p.getInventory().firstEmpty();
				// inventory full
				if(firstEmpty == -1) {
					invFull = true;
					break;
				}
				ItemStack stack = mailGetter.getPlayerMail(player);
				if(stack == null) break;
				p.getInventory().addItem(new ItemStack[] { stack });
				WebAuctionPlus.doUpdateInventory(p);
//				delMail.add(mail.getMailId());
				gotMail++;
			}
		} catch(Exception e) {
			p.sendMessage(WebAuctionPlus.chatPrefix+ChatColor.RED+"Error getting items!");
			e.printStackTrace();
		}
//		WebAuctionPlus.dataQueries.deleteMail(player, delMail);
		if(gotMail > 0)	p.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("got_mail"));
		else			p.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("no_mail"));
		if(invFull)		p.sendMessage(WebAuctionPlus.chatPrefix+WebAuctionPlus.Lang.getString("inventory_full"));
		return true;
	}


	// shout sign
	private static long lastUseShout = 0;
	public static void clickSignShout() {
		if(lastUseShout+(10*60*1000) > WebAuctionPlus.getCurrentMilli()) return;
		lastUseShout = WebAuctionPlus.getCurrentMilli();
		Random generator = new Random();
		while(true) {
			int roll = generator.nextInt(6);
			switch(roll) {
				case 0: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"All your base are belong to Notch!"); return;
				case 1: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"I like chocolate milk!"); return;
				case 2: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"I like potatos."); return;
				case 3: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"Hm, my finger points."); return;
				case 4: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"BAGOCK! I sorry, I thought you was corn."); return;
				case 5: Bukkit.getServer().broadcastMessage(WebAuctionPlus.chatPrefix+"Hey, there's a creeper behind you."); return;
			}
		}
//case 0: p.sendMessage(WebAuctionPlus.chatPrefix + "RAAN MIR TAH!"); break;
//case 1: p.sendMessage(WebAuctionPlus.chatPrefix + "LAAS YAH NIR!"); break;
//case 2: p.sendMessage(WebAuctionPlus.chatPrefix + "FEIM ZII GRON!"); break;
//case 3: p.sendMessage(WebAuctionPlus.chatPrefix + "OD AH VIING!"); break;
//case 4: p.sendMessage(WebAuctionPlus.chatPrefix + "HUN KAL ZOOR!"); break;
//case 5: p.sendMessage(WebAuctionPlus.chatPrefix + "LOK VAH KOOR!"); break;
//case 6: p.sendMessage(WebAuctionPlus.chatPrefix + "ZUN HAAL VIK!"); break;
//case 7: p.sendMessage(WebAuctionPlus.chatPrefix + "FAAS RU MAAR!"); break;
//case 8: p.sendMessage(WebAuctionPlus.chatPrefix + "JOOR ZAH FRUL!"); break;
//case 9: p.sendMessage(WebAuctionPlus.chatPrefix + "SU GRAH DUN!"); break;
//case 10: p.sendMessage(WebAuctionPlus.chatPrefix + "YOL TOOR SHOL!"); break;
//case 11: p.sendMessage(WebAuctionPlus.chatPrefix + "FO KRAH DIIN!"); break;
//case 12: p.sendMessage(WebAuctionPlus.chatPrefix + "LIZ SLEN NUS!"); break;
//case 13: p.sendMessage(WebAuctionPlus.chatPrefix + "KAAN DREM OV!"); break;
//case 14: p.sendMessage(WebAuctionPlus.chatPrefix + "KRII LUN AUS!"); break;
//case 15: p.sendMessage(WebAuctionPlus.chatPrefix + "TIID KLO UL!"); break;
//case 16: p.sendMessage(WebAuctionPlus.chatPrefix + "STRUN BAH QO!"); break;
//case 17: p.sendMessage(WebAuctionPlus.chatPrefix + "ZUL MEY GUT!"); break;
//case 18: p.sendMessage(WebAuctionPlus.chatPrefix + "WULK NAH KEST!"); break;
//default: p.sendMessage(WebAuctionPlus.chatPrefix + "FUS RO DAH!"); break;
	}


}
