package me.lorenzop.webauctionplus.dao;

import java.util.Map;

import me.lorenzop.webauctionplus.WebAuctionPlus;

import org.bukkit.enchantments.Enchantment;
import org.bukkit.inventory.ItemStack;

public class MailItem {

	public boolean timEnabled = false;
	private int mailId		= 0;
	private String player	= null;
	private ItemStack stack = null;

	public MailItem() {
	}
	public MailItem(boolean timEnabled) {
		this.timEnabled = timEnabled;
	}

	// mail id
	public int getMailId() {
		return mailId;
	}
	public void setMailId(int mailId) {
		this.mailId = mailId;
	}

	// player name
	public String getPlayerName() {
		return player;
	}
	public void setPlayerName(String player) {
		this.player = player;
	}

	// item stack
	public ItemStack getItemStack() {
		return stack;
	}
	public void setItemStack(ItemStack stack) {
		this.stack = stack;
	}

	// enchantments
	public boolean isEnchanted() {
		return stack.getEnchantments().isEmpty();
	}
	public Map<Enchantment, Integer> getEnchantments() {
		return stack.getEnchantments();
	}
	public void addEnchantments(ItemStack stack, String enchantment, int level) {
		if (stack == null || enchantment.isEmpty()) return;
		Enchantment ench = Enchantment.getByName(enchantment);
		if (!timEnabled && !ench.canEnchantItem(stack)) {
			WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Removed unsafe enchantment: "+ench.toString());
			return;
		}
		if (level < 1) level = 1;
		if (timEnabled) {
			if (level > 127) level = 127;
			stack.addUnsafeEnchantment(ench, level);
		} else {
			if (level > ench.getMaxLevel()) {
				WebAuctionPlus.log.warning(WebAuctionPlus.logPrefix+"Lowering unsafe enchantment from level "+
						level+" to "+ench.getMaxLevel()+" "+ench.toString());
				level = ench.getMaxLevel();
			}
			stack.addEnchantment(ench, level);
		}
	}
//function not used
//	public void addEnchantments(ItemStack stack, Map<Enchantment, Integer> Enchantments) {
//		if (stack == null || Enchantments.isEmpty()) return;
//		for (Map.Entry<Enchantment, Integer> entry : Enchantments.entrySet()) {
//			if (entry.getKey().canEnchantItem(stack))
//				addEnchantments(stack, entry.getKey().getName(), (int)entry.getValue());
//		}
//	}

}