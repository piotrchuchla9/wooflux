import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, ToggleControl, TextControl } from "@wordpress/components";
import { Placeholder } from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
  const { showCategories, showPrice, showOnSale, showInStock } = attributes;
  const blockProps = useBlockProps({ className: "wooflux-filter-panel-editor" });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Filter Options", "wooflux")}>
          <ToggleControl
            label={__("Show categories", "wooflux")}
            checked={showCategories}
            onChange={(val) => setAttributes({ showCategories: val })}
          />
          <ToggleControl
            label={__("Show price range", "wooflux")}
            checked={showPrice}
            onChange={(val) => setAttributes({ showPrice: val })}
          />
          <ToggleControl
            label={__("Show on-sale filter", "wooflux")}
            checked={showOnSale}
            onChange={(val) => setAttributes({ showOnSale: val })}
          />
          <ToggleControl
            label={__("Show in-stock filter", "wooflux")}
            checked={showInStock}
            onChange={(val) => setAttributes({ showInStock: val })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <Placeholder
          icon="filter"
          label={__("WooFlux Filter Panel", "wooflux")}
          instructions={__(
            "Filter panel renders on the frontend. Configure options in the sidebar.",
            "wooflux"
          )}
        />
      </div>
    </>
  );
}
