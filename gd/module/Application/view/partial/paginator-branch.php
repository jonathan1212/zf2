<?php if ($this->pageCount): ?>
     <div>
         <ul class="pagination">
             <!-- Previous page link -->
             <?php if (isset($this->previous)): ?>
                 <li>
                     <a href="<?php echo $this->url($this->route); ?>?p=<?php echo $this->previous; ?>">
                         <<
                     </a>
                 </li>
             <?php else: ?>
                 <li class="disabled">
                     <a href="#">
                         <<
                     </a>
                 </li>
             <?php endif; ?>

             <!-- Numbered page links -->
             <?php foreach ($this->pagesInRange as $page): ?>
                 <?php if ($page != $this->current): ?>
                     <li>
                         <a href="<?php echo $this->url($this->route);?>?p=<?php echo $page; ?>">
                             <?php echo $page; ?>
                         </a>
                     </li>
                 <?php else: ?>
                     <li class="active">
                         <a href="#"><?php echo $page; ?></a>
                     </li>
                 <?php endif; ?>
             <?php endforeach; ?>

             <!-- Next page link -->
             <?php if (isset($this->next)): ?>
                 <li>
                     <a href="<?php echo $this->url($this->route); ?>?p=<?php echo $this->next; ?>">
                         >>
                     </a>
                 </li>
             <?php else: ?>
                 <li class="disabled">
                     <a href="#">
                         >>
                     </a>
                 </li>
             <?php endif; ?>
         </ul>
     </div>
 <?php endif; ?>