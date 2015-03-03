<?php

/* This file displays a list of links to pages of shirts. It needs to
 * receive the total number of pages ($total_pages) and the current
 * page number ($current_page). It will display all the numbers between
 * 1 and $total_pages, and it will make all but $current_page a link.
 */

?>
				<div class="pagination">

					<?php $i = 0; ?>
					<?php while ($i < $total_pages) : ?>
						<?php $i += 1; ?>
						<?php if ($i == $current_page) : ?>
							<span><?php echo $i; ?></span>
						<?php else : ?>
							<a href="./?pg=<?php echo $i; ?>"><?php echo $i; ?></a>
						<?php endif; ?>
					<?php endwhile; ?>

				</div>