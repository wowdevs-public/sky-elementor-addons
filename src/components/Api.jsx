import React, { useState, useEffect } from 'react';
import { __ } from "@wordpress/i18n";
import axios from 'axios';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

import PageHeader from './includes/PageHeader';

const Api = () => {
  

  return (
      <div className="mt-12 pt-6">
        <div className="mb-12 relative flex flex-col bg-clip-border rounded-xl bg-white dark:bg-gray-900 text-gray-700 shadow-sm">
          <PageHeader
            title="API Credentials"
            desc="Manage your API credentials and settings."
          />
          <div className="p-8">
          API
          </div>
        </div>
      </div>
  );
};

export default Api;
