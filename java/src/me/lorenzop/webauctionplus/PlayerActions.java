package me.lorenzop.webauctionplus;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import me.lorenzop.webauctionplus.dao.AuctionItem;
import me.lorenzop.webauctionplus.dao.MailItem;

import org.bukkit.ChatColor;
import org.bukkit.enchantments.Enchantment;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;

public class PlayerActions {

	private final WebAuctionPlus plugin;

	public PlayerActions(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	// deposit items
	public synchronized boolean DepositStack(Player p) {
		String player = p.getName();
		// get item/stack in hand
		ItemStack stack = p.getItemInHand();
		int itemTypeId = stack.getTypeId();
		// no item in hand
		if (itemTypeId == 0) {
			p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_item_in_hand"));
			return false;
		}
		int damage = stack.getDurability();
		if (damage < 0) damage = 0;
		Map<Enchantment, Integer> ench = stack.getEnchantments();

		try {
			// get player items from db
			List<AuctionItem> auctionItems = plugin.dataQueries.GetItems(player, itemTypeId, stack.getDurability(), false);
			// loop items from db
			int foundItemId = -1;
			for (AuctionItem auctionItem : auctionItems) {
				// same item id
				if (auctionItem.getTypeId() != itemTypeId) continue;
				// same damage
				if (auctionItem.getDamage() != damage) continue;
				// same enchantments
				if (!plugin.dataQueries.ItemHasEnchantments(auctionItem.getItemId(), ench)) continue;
				foundItemId = auctionItem.getItemId();
				break;
			}

			// add new item
			if (foundItemId == -1) {
				plugin.dataQueries.CreateItem(player, stack);
			// add to existing item
			} else {
				plugin.dataQueries.AddItemQuantity(foundItemId, stack.getAmount());
			}
			p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("item_stack_stored"));
			p.setItemInHand(null);
			return true;
		} catch (Exception e) {
			e.printStackTrace();
		}
		return false;
	}

	// withdraw items
	public boolean WithdrawStacks(Player p) {
		return WithdrawStacks(p, 0);
	}
	public synchronized boolean WithdrawStacks(Player p, int qty) {
		String player = p.getName();
		if(qty<1 || qty>36) qty = 36;
		List<Integer> delMail = new ArrayList<Integer>();
		int nextId = 0;
		try {
			MailItem mail = null;
			boolean invFull = false;
			boolean gotMail = false;
			// get items from mailbox
			for (int i=0; i<qty; i++) {
				mail = plugin.dataQueries.getMail(player, nextId);
				if (mail == null) break;
				int firstEmpty = p.getInventory().firstEmpty();
				// inventory full
				if (firstEmpty == -1) {
					invFull = true;
					break;
				}
				p.getInventory().addItem(new ItemStack[] { mail.getItemStack() });
				plugin.doUpdateInventory(p);
				delMail.add(mail.getMailId());
				nextId = mail.getMailId();
				gotMail = true;
			}
			plugin.dataQueries.deleteMail(player, delMail);
			if (gotMail)
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("got_mail"));
			else
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("no_mail"));
			if (invFull)
				p.sendMessage(WebAuctionPlus.chatPrefix + WebAuctionPlus.Lang.getString("inventory_full"));
		} catch(Exception e) {
			p.sendMessage(WebAuctionPlus.chatPrefix + ChatColor.RED + "Error getting items!");
			e.printStackTrace();
		}
		return false;
	}

}
