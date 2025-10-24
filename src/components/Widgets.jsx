import React, { useState } from 'react';
import { __ } from "@wordpress/i18n";
import PageHeader from './includes/PageHeader';
import RenderFeatures from './includes/RenderFeatures';

const Widgets = ({ isWizard = false }) => {

  return (
    <div className="mt-6 pt-6">
      <div className="mb-12 relative flex flex-col bg-clip-border rounded-xl bg-white dark:bg-gray-900 text-gray-700 shadow-sm">
        <PageHeader
          title="Widgets List"
          desc="It is important to be aware of your system settings and make sure that they are correctly configured for optimal performance." />
        <div className="p-3 md:p-6">
          <RenderFeatures featuresType="get_widgets" />
        </div>
      </div>
    </div>
  );
};

export default Widgets;
