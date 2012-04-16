package me.lorenzop.webauctionplus;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import me.lorenzop.webauctionplus.dao.AuctionItem;
import me.lorenzop.webauctionplus.dao.MailItem;

import org.bukkit.enchantments.Enchantment;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;

public class PlayerActions {

	private WebAuctionPlus plugin;
	public PlayerActions(WebAuctionPlus plugin) {
		this.plugin = plugin;
	}

	public boolean DepositStack(Player p) {
		String player = p.getName();
		// get item/stack in hand
		ItemStack stack = p.getItemInHand();
		int itemTypeId = stack.getTypeId();
		// no item in hand
		if (itemTypeId == 0) {
			p.sendMessage(WebAuctionPlus.chatPrefix + "Please hold a stack of items in your hand and  right click to deposit them.");
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
			p.sendMessage(WebAuctionPlus.chatPrefix + "Item stack stored.");
			p.setItemInHand(null);
			return true;
		} catch (Exception e) {
			e.printStackTrace();
		}
		return false;
	}

	public boolean WithdrawStacks(Player p) {
		String player = p.getName();
		List<Integer> delMail = new ArrayList<Integer>();
		int nextId = 0;
		try {
			MailItem mail = null;
			boolean invFull = false;
			boolean gotMail = false;
			// get items from mailbox
			for (int i=0; i<36; i++) {
				mail = plugin.dataQueries.getMail(player, nextId);
				if (mail == null) break;
				int firstEmpty = p.getInventory().firstEmpty();
				// inventory full
				if (firstEmpty == -1) {
					invFull = true;
					break;
				}
				p.getInventory().addItem(new ItemStack[] { mail.getItemStack() });
				doUpdateInventory(p);
				delMail.add(mail.getMailId());
				nextId = mail.getMailId();
				gotMail = true;
			}
			plugin.dataQueries.deleteMail(player, delMail);
			if (gotMail)
				p.sendMessage(WebAuctionPlus.chatPrefix + "Mail retrieved");
			else
				p.sendMessage(WebAuctionPlus.chatPrefix + "No mail");
			if (invFull)
				p.sendMessage(WebAuctionPlus.chatPrefix + "Your inventory is full");
		} catch(Exception e) {
			e.printStackTrace();
		}
		return false;
//			List<Integer> enchantments = new ArrayList<Integer>();
//			List<Integer> enchLevels = new ArrayList<Integer>();
//			List<Integer> enchIDs = plugin.dataQueries.getEnchantmentsForItem(mail.getId(), ItemTables.Mail);
//			if (p.getInventory().firstEmpty() != -1) {

//				ItemStack stack = mail.getItemStack();
//				for (int enchantID : enchIDs) {
//					Map<Integer, Integer> enchMap = plugin.dataQueries.getEnchantIDLevel(enchantID);
//					for (Map.Entry<Integer, Integer> entry : enchMap.entrySet()) {
//						Enchantment tempEnch = Enchantment.getById(((Integer)entry.getKey()).intValue());
//						// p.sendMessage(tempEnch.getName()+" "+resultEnch.getInt("level"));
//						if (tempEnch.canEnchantItem(stack)) {
//							enchantments.add((Integer)entry.getKey());
//							enchLevels.add((Integer)entry.getValue());
//							enchanted = true;
//						} else {
//							plugin.log.info(plugin.logPrefix  + "Can't enchant for some reason");
//							p.sendMessage(  plugin.chatPrefix + "Can't enchant for some reason");
//						}
//					}
//				}
//				plugin.dataQueries.deleteMail(mail.getId());
//				int firstEmpty = -1;
//				if (enchanted) {
//					firstEmpty = p.getInventory().firstEmpty();
//				}
//				p.getInventory().addItem(new ItemStack[] {
//					stack
//				});
//				if (enchanted) {
//					ItemStack tempStack = p.getInventory().getItem(firstEmpty);
//					Iterator<Integer> itr = enchantments.iterator();
//					Iterator<Integer> itrl = enchLevels.iterator();
//					while ( (itr.hasNext()) && (itrl.hasNext()) ) {
//						Enchantment tempEnch = Enchantment.getById(itr.next());
//						tempStack.addEnchantment(tempEnch, ((Integer)itrl.next()).intValue());
//					}
//				}
//				doUpdateInventory(p);
//				gotMail = true;
//				invFull = false;
//			} else {
//				p.sendMessage(plugin.chatPrefix + "Inventory full, cannot get mail");
//				invFull = true;
//			}
//			if (invFull) {
//				break;
//			}
//		}
	}

	@SuppressWarnings("deprecation")
	public void doUpdateInventory(Player p) {
		p.updateInventory();
	}

}
