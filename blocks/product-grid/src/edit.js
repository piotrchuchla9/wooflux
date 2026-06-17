import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, RangeControl } from "@wordpress/components";
import { Placeholder } from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
  const { columns } = attributes;
  const blockProps = useBlockProps({ className: "wooflux-product-grid-editor" });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Grid Options", "wooflux")}>
          <RangeControl
            label={__("Columns", "wooflux")}
            value={columns}
            onChange={(val) => setAttributes({ columns: val })}
            min={1}
            max={6}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <Placeholder
          icon="grid-view"
          label={__("WooFlux Product Grid", "wooflux")}
          instructions={__(
            "Products render on the frontend and update reactively when filters change.",
            "wooflux"
          )}
        />
      </div>
    </>
  );
}
