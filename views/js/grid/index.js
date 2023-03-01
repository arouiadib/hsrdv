import Grid from '@components/grid/grid';
import LinkRowActionExtension from '@components/grid/extension/link-row-action-extension';
import SubmitRowActionExtension from '@components/grid/extension/action/row/submit-row-action-extension';
import SortingExtension from "@components/grid/extension/sorting-extension";
import PositionExtension from "@components/grid/extension/position-extension";

const $ = window.$;

$(() => {
  let gridDivs = document.querySelectorAll('.js-grid');
  gridDivs.forEach((gridDiv) => {
      const linkBlockGrid = new Grid(gridDiv.dataset.gridId);

      linkBlockGrid.addExtension(new SortingExtension());
      linkBlockGrid.addExtension(new LinkRowActionExtension());
      linkBlockGrid.addExtension(new SubmitRowActionExtension());
      linkBlockGrid.addExtension(new PositionExtension());
  });
});
