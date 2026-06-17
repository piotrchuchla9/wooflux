import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
  PanelBody,
  RangeControl,
  SelectControl,
  Placeholder,
} from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
  const { columns, layout } = attributes;
  const blockProps = useBlockProps({ className: "wooflux-product-grid-editor" });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Grid Options", "wooflux")}>
          <SelectControl
            label={__("Layout", "wooflux")}
            value={layout}
            options={[
              { label: __("Grid", "wooflux"), value: "grid" },
              { label: __("List", "wooflux"), value: "list" },
            ]}
            onChange={(val) => setAttributes({ layout: val })}
          />
          {layout === "grid" && (
            <RangeControl
              label={__("Columns", "wooflux")}
              value={columns}
              onChange={(val) => setAttributes({ columns: val })}
              min={1}
              max={6}
            />
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <Placeholder
          icon={layout === "list" ? "list-view" : "grid-view"}
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
