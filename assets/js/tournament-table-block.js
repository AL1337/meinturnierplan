(function(wp) {
  const { registerBlockType } = wp.blocks;
  const { createElement: el, Fragment, useState, useEffect } = wp.element;
  const { SelectControl, Placeholder, Spinner } = wp.components;
  const { __ } = wp.i18n;
  const { apiFetch } = wp;

  registerBlockType('meinturnierplan/tournament-table', {
    title: __('Tournament Table', 'meinturnierplan'),
    icon: 'editor-table',
    category: 'widgets',
    description: __('Display a tournament table from your custom post types.', 'meinturnierplan'),
    
    attributes: {
      tableId: {
        type: 'string',
        default: ''
      },
      tableName: {
        type: 'string',
        default: ''
      }
    },

    edit: function(props) {
      const { attributes, setAttributes } = props;
      const { tableId, tableName } = attributes;
      const [tables, setTables] = useState([]);
      const [loading, setLoading] = useState(true);

      // Fetch tournament tables on component mount
      useEffect(() => {
        const formData = new FormData();
        formData.append('action', 'mtp_get_tables');
        formData.append('nonce', mtpBlock.nonce);

        fetch(mtpBlock.ajaxUrl, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            setTables(data.data);
          }
          setLoading(false);
        })
        .catch(error => {
          console.error('Error fetching tables:', error);
          setLoading(false);
        });
      }, []);

      const onChangeTable = function(value) {
        const selectedTable = tables.find(table => table.value === value);
        setAttributes({
          tableId: value,
          tableName: selectedTable ? selectedTable.label : ''
        });
      };

      if (loading) {
        return el(
          Placeholder,
          {
            icon: 'editor-table',
            label: __('Tournament Table', 'meinturnierplan')
          },
          el(Spinner)
        );
      }

      return el(
        Fragment,
        null,
        el(
          Placeholder,
          {
            icon: 'editor-table',
            label: __('Tournament Table', 'meinturnierplan'),
            instructions: tableId 
              ? __('Tournament table selected: ', 'meinturnierplan') + tableName
              : __('Choose a tournament table to display.', 'meinturnierplan')
          },
          el(SelectControl, {
            label: __('Select Tournament Table', 'meinturnierplan'),
            value: tableId,
            options: tables,
            onChange: onChangeTable
          })
        )
      );
    },

    save: function() {
      // Return null since we use dynamic rendering
      return null;
    }
  });

})(window.wp);
