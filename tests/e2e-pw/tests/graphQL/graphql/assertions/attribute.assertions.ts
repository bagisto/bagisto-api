import { expect } from '@playwright/test';

export const assertAttributeNode = (attribute: any) => {
  expect(attribute).not.toBeNull();

  expect(attribute.id).toBeDefined();
  expect(typeof attribute.id).toBe('string');
  expect(attribute.id.length).toBeGreaterThan(0);

  expect(attribute._id).toBeDefined();
  expect(typeof attribute._id === 'string' || typeof attribute._id === 'number').toBeTruthy();

  expect(attribute.code).toBeDefined();
  expect(typeof attribute.code).toBe('string');
  expect(attribute.code.length).toBeGreaterThan(0);

  expect(attribute.adminName).toBeDefined();
  expect(typeof attribute.adminName).toBe('string');
  expect(attribute.adminName.length).toBeGreaterThan(0);

  if (attribute.type !== undefined) {
    expect(typeof attribute.type === 'string' || typeof attribute.type === 'number').toBeTruthy();
  }

  if (attribute.swatchType !== undefined) {
    expect(typeof attribute.swatchType === 'string' || attribute.swatchType === null).toBeTruthy();
  }

  if (attribute.position !== undefined) {
    expect(typeof attribute.position).toBe('number');
  }

  if (attribute.isRequired !== undefined) {
    expect(typeof attribute.isRequired).toBe('boolean');
  }

  if (attribute.isConfigurable !== undefined) {
    expect(typeof attribute.isConfigurable).toBe('boolean');
  }

  if (attribute.options !== undefined) {
    expect(attribute.options).toBeDefined();
    expect(attribute.options).toHaveProperty('edges');
    expect(attribute.options).toHaveProperty('totalCount');
    expect(typeof attribute.options.totalCount).toBe('number');
    expect(attribute.options.totalCount).toBeGreaterThanOrEqual(0);

    expect(Array.isArray(attribute.options.edges)).toBeTruthy();
    attribute.options.edges.forEach((edge: any) => {
      expect(edge.node).toBeDefined();
      expect(edge.node.id).toBeDefined();
      expect(edge.node.adminName).toBeDefined();
    });
  }
};

export const assertAttributeNodeFull = (attribute: any) => {
  expect(attribute).not.toBeNull();

  expect(attribute.id).toBeDefined();
  expect(attribute._id).toBeDefined();
  expect(attribute.code).toBeDefined();
  expect(attribute.adminName).toBeDefined();
  expect(attribute.type).toBeDefined();

  if (attribute.isRequired !== undefined) {
    expect(typeof attribute.isRequired).toBe('boolean');
  }
  if (attribute.isConfigurable !== undefined) {
    expect(typeof attribute.isConfigurable).toBe('boolean');
  }
  if (attribute.isUnique !== undefined) {
    expect(typeof attribute.isUnique).toBe('boolean');
  }
  if (attribute.isFilterable !== undefined) {
    expect(typeof attribute.isFilterable).toBe('boolean');
  }
  if (attribute.isComparable !== undefined) {
    expect(typeof attribute.isComparable).toBe('boolean');
  }
  if (attribute.isUserDefined !== undefined) {
    expect(typeof attribute.isUserDefined).toBe('boolean');
  }
  if (attribute.isVisibleOnFront !== undefined) {
    expect(typeof attribute.isVisibleOnFront).toBe('boolean');
  }
  if (attribute.valuePerLocale !== undefined) {
    expect(typeof attribute.valuePerLocale).toBe('boolean');
  }
  if (attribute.valuePerChannel !== undefined) {
    expect(typeof attribute.valuePerChannel).toBe('boolean');
  }
  if (attribute.enableWysiwyg !== undefined) {
    expect(typeof attribute.enableWysiwyg).toBe('boolean');
  }

  expect(typeof attribute.position).toBe('number');
  expect(typeof attribute.createdAt).toBe('string');
  expect(typeof attribute.updatedAt).toBe('string');

  if (attribute.swatchType !== undefined) {
    expect(typeof attribute.swatchType === 'string' || attribute.swatchType === null).toBeTruthy();
  }
  if (attribute.validation !== undefined) {
    expect(typeof attribute.validation === 'string' || attribute.validation === null).toBeTruthy();
  }
  if (attribute.regex !== undefined) {
    expect(typeof attribute.regex === 'string' || attribute.regex === null).toBeTruthy();
  }
  if (attribute.defaultValue !== undefined) {
    const validTypes = ['string', 'boolean', 'number', 'object'];
    const isValidType = validTypes.includes(typeof attribute.defaultValue) || attribute.defaultValue === null;
    if (!isValidType) {
      console.log(`Warning: defaultValue has unexpected type: ${typeof attribute.defaultValue}, value:`, attribute.defaultValue);
    }
    expect(isValidType).toBeTruthy();
  }
  if (attribute.columnName !== undefined) {
    expect(typeof attribute.columnName === 'string' || attribute.columnName === null).toBeTruthy();
  }
  if (attribute.validations !== undefined) {
    expect(typeof attribute.validations === 'string' || attribute.validations === null).toBeTruthy();
  }

  if (attribute.options !== undefined) {
    expect(attribute.options).toBeDefined();
    expect(attribute.options).toHaveProperty('edges');
    expect(attribute.options).toHaveProperty('totalCount');
    expect(typeof attribute.options.totalCount).toBe('number');

    expect(Array.isArray(attribute.options.edges)).toBeTruthy();
    attribute.options.edges.forEach((edge: any) => {
      expect(edge.node).toBeDefined();
      expect(edge.node.id).toBeDefined();
      expect(edge.node.adminName).toBeDefined();
      expect(edge.node.sortOrder).toBeDefined();

      if (edge.node.swatchValue !== undefined) {
        expect(typeof edge.node.swatchValue === 'string' || edge.node.swatchValue === null).toBeTruthy();
      }

      if (edge.node.swatchValueUrl !== undefined) {
        expect(typeof edge.node.swatchValueUrl === 'string' || edge.node.swatchValueUrl === null).toBeTruthy();
      }

      if (edge.node.translation !== undefined && edge.node.translation !== null) {
        expect(edge.node.translation).toHaveProperty('id');
        expect(edge.node.translation).toHaveProperty('locale');
        expect(edge.node.translation).toHaveProperty('label');
      }

      if (edge.node.translations !== undefined) {
        expect(edge.node.translations).toHaveProperty('edges');
        expect(edge.node.translations).toHaveProperty('totalCount');
        expect(edge.node.translations).toHaveProperty('pageInfo');

        if (edge.node.translations.edges.length > 0) {
          edge.node.translations.edges.forEach((transEdge: any) => {
            expect(transEdge.node).toBeDefined();
            expect(transEdge.node.id).toBeDefined();
            expect(transEdge.node.locale).toBeDefined();
            expect(transEdge.node.label).toBeDefined();
          });
        }
      }

      expect(edge.cursor).toBeDefined();
    });

    expect(attribute.options.pageInfo).toBeDefined();
    expect(attribute.options.pageInfo.endCursor).toBeDefined();
    expect(typeof attribute.options.pageInfo.hasNextPage).toBe('boolean');
  }

  if (attribute.translations !== undefined) {
    expect(attribute.translations).toBeDefined();
    expect(attribute.translations).toHaveProperty('edges');
    expect(attribute.translations).toHaveProperty('totalCount');
    expect(attribute.translations).toHaveProperty('pageInfo');

    if (attribute.translations.edges.length > 0) {
      attribute.translations.edges.forEach((edge: any) => {
        expect(edge.node).toBeDefined();
        expect(edge.node.id).toBeDefined();
        expect(edge.node.attributeId).toBeDefined();
        expect(edge.node.locale).toBeDefined();
        expect(edge.node.name).toBeDefined();
      });
    }

    expect(attribute.translations.pageInfo).toBeDefined();
    expect(attribute.translations.pageInfo.endCursor).toBeDefined();
    expect(typeof attribute.translations.pageInfo.hasNextPage).toBe('boolean');
  }
};

export const assertAttributesResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributes');
  expect(body.data.attributes).not.toBeNull();

  const attributes = body.data.attributes;

  expect(attributes).toHaveProperty('edges');
  expect(Array.isArray(attributes.edges)).toBeTruthy();
  expect(attributes.edges.length).toBeGreaterThan(0);

  attributes.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    assertAttributeNode(edge.node);
    expect(edge.cursor).toBeDefined();
  });

  expect(attributes.pageInfo).toBeDefined();
  expect(attributes.pageInfo).toHaveProperty('endCursor');
  expect(attributes.pageInfo).toHaveProperty('hasNextPage');
  expect(typeof attributes.pageInfo.hasNextPage).toBe('boolean');

  expect(attributes).toHaveProperty('totalCount');
  expect(typeof attributes.totalCount).toBe('number');
  expect(attributes.totalCount).toBeGreaterThan(0);
};

export const assertAttributesResponseFull = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributes');
  expect(body.data.attributes).not.toBeNull();

  const attributes = body.data.attributes;

  expect(attributes).toHaveProperty('edges');
  expect(Array.isArray(attributes.edges)).toBeTruthy();
  expect(attributes.edges.length).toBeGreaterThan(0);

  attributes.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    assertAttributeNodeFull(edge.node);
    expect(edge.cursor).toBeDefined();
  });

  expect(attributes.pageInfo).toBeDefined();
  expect(attributes.pageInfo).toHaveProperty('endCursor');
  expect(attributes.pageInfo).toHaveProperty('startCursor');
  expect(attributes.pageInfo).toHaveProperty('hasNextPage');
  expect(attributes.pageInfo).toHaveProperty('hasPreviousPage');
  expect(typeof attributes.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof attributes.pageInfo.hasPreviousPage).toBe('boolean');

  expect(attributes).toHaveProperty('totalCount');
  expect(typeof attributes.totalCount).toBe('number');
  expect(attributes.totalCount).toBeGreaterThan(0);
};

export const assertAttributesWithFirstLimit = (body: any, first: number) => {
  assertAttributesResponse(body);
  
  const edges = body.data.attributes.edges;
  expect(edges.length).toBeLessThanOrEqual(first);
};

export const assertAttributesWithOptions = (body: any) => {
  assertAttributesResponse(body);

  const edges = body.data.attributes.edges;
  let hasAttributesWithOptions = false;
  let attributesWithOptionsCount = 0;

  edges.forEach((edge: any) => {
    if (edge.node.options && 
        edge.node.options.edges && 
        edge.node.options.edges.length > 0) {
      hasAttributesWithOptions = true;
      attributesWithOptionsCount++;
      
      edge.node.options.edges.forEach((optionEdge: any) => {
        expect(optionEdge.node.id).toBeDefined();
        expect(optionEdge.node.adminName).toBeDefined();
      });
    }
  });

  if (!hasAttributesWithOptions) {
    console.log('Warning: No attributes with options found in the response. This may be expected if the test data does not include attribute options.');
  } else {
    console.log(`Found ${attributesWithOptionsCount} attribute(s) with options`);
  }
};

export const assertAttributesWithTranslations = (body: any) => {
  assertAttributesResponseFull(body);

  const edges = body.data.attributes.edges;
  let hasAttributesWithTranslations = false;

  edges.forEach((edge: any) => {
    if (edge.node.translations && edge.node.translations.totalCount > 0) {
      hasAttributesWithTranslations = true;
      expect(edge.node.translations.edges.length).toBeGreaterThan(0);
      
      edge.node.translations.edges.forEach((transEdge: any) => {
        expect(transEdge.node.id).toBeDefined();
        expect(transEdge.node.attributeId).toBeDefined();
        expect(transEdge.node.locale).toBeDefined();
        expect(transEdge.node.name).toBeDefined();
      });
    }
  });

};

export const assertAttributesWithOptionTranslations = (body: any) => {
  assertAttributesResponseFull(body);

  const edges = body.data.attributes.edges;
  let hasOptionsWithTranslations = false;

  edges.forEach((edge: any) => {
    if (edge.node.options && edge.node.options.edges.length > 0) {
      edge.node.options.edges.forEach((optionEdge: any) => {
        if (optionEdge.node.translation !== undefined && optionEdge.node.translation !== null) {
          hasOptionsWithTranslations = true;
          expect(optionEdge.node.translation.locale).toBeDefined();
          expect(optionEdge.node.translation.label).toBeDefined();
        }

        if (optionEdge.node.translations && optionEdge.node.translations.edges.length > 0) {
          hasOptionsWithTranslations = true;
          optionEdge.node.translations.edges.forEach((transEdge: any) => {
            expect(transEdge.node.locale).toBeDefined();
            expect(transEdge.node.label).toBeDefined();
          });
        }
      });
    }
  });

  expect(body.data.attributes).toBeDefined();
};

export const assertGraphQLETypedError = (body: any, expectedMessage?: string) => {
  expect(body).toHaveProperty('errors');
  expect(Array.isArray(body.errors)).toBeTruthy();
  expect(body.errors.length).toBeGreaterThan(0);

  if (expectedMessage) {
    const errorMessages = body.errors.map((error: any) => error.message);
    expect(errorMessages).toContain(expectedMessage);
  }
};

export const assertEmptyAttributesResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributes');
  
  const attributes = body.data.attributes;
  expect(attributes.edges).toBeDefined();
  expect(Array.isArray(attributes.edges)).toBeTruthy();
  expect(attributes.edges.length).toBe(0);
  
  expect(attributes.totalCount).toBeDefined();
  expect(attributes.totalCount).toBe(0);
};

export const assertAttributeByIdNode = (attribute: any) => {
  expect(attribute).not.toBeNull();

  expect(attribute.id).toBeDefined();
  expect(typeof attribute.id).toBe('string');
  expect(attribute.id.length).toBeGreaterThan(0);

  expect(attribute._id).toBeDefined();
  expect(typeof attribute._id).toBe('string');

  expect(attribute.code).toBeDefined();
  expect(typeof attribute.code).toBe('string');
  expect(attribute.code.length).toBeGreaterThan(0);

  expect(attribute.adminName).toBeDefined();
  expect(typeof attribute.adminName).toBe('string');
  expect(attribute.adminName.length).toBeGreaterThan(0);

  expect(attribute.type).toBeDefined();
  expect(typeof attribute.type).toBe('string');

  expect(attribute.isRequired).toBeDefined();
  expect(typeof attribute.isRequired).toBe('boolean');

  expect(attribute.isUnique).toBeDefined();
  expect(typeof attribute.isUnique).toBe('boolean');

  expect(attribute.isFilterable).toBeDefined();
  expect(typeof attribute.isFilterable).toBe('boolean');

  expect(attribute.isComparable).toBeDefined();
  expect(typeof attribute.isComparable).toBe('boolean');

  expect(attribute.isConfigurable).toBeDefined();
  expect(typeof attribute.isConfigurable).toBe('boolean');

  expect(attribute.isUserDefined).toBeDefined();
  expect(typeof attribute.isUserDefined).toBe('boolean');

  expect(attribute.isVisibleOnFront).toBeDefined();
  expect(typeof attribute.isVisibleOnFront).toBe('boolean');

  expect(attribute.valuePerLocale).toBeDefined();
  expect(typeof attribute.valuePerLocale).toBe('boolean');

  expect(attribute.valuePerChannel).toBeDefined();
  expect(typeof attribute.valuePerChannel).toBe('boolean');

  expect(attribute.enableWysiwyg).toBeDefined();
  expect(typeof attribute.enableWysiwyg).toBe('boolean');

  expect(attribute.position).toBeDefined();
  expect(typeof attribute.position).toBe('number');

  expect(attribute.createdAt).toBeDefined();
  expect(typeof attribute.createdAt).toBe('string');

  expect(attribute.updatedAt).toBeDefined();
  expect(typeof attribute.updatedAt).toBe('string');

  if (attribute.swatchType !== undefined && attribute.swatchType !== null) {
    expect(typeof attribute.swatchType).toBe('string');
  }

  if (attribute.validation !== undefined && attribute.validation !== null) {
    expect(typeof attribute.validation).toBe('string');
  }

  if (attribute.regex !== undefined && attribute.regex !== null) {
    expect(typeof attribute.regex).toBe('string');
  }

  if (attribute.defaultValue !== undefined && attribute.defaultValue !== null) {
    expect(typeof attribute.defaultValue).toBe('string');
  }

  if (attribute.columnName !== undefined && attribute.columnName !== null) {
    expect(typeof attribute.columnName).toBe('string');
  }

  if (attribute.validations !== undefined && attribute.validations !== null) {
    expect(typeof attribute.validations).toBe('string');
  }
};

export const assertGetAttributeByIdResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attribute');
  expect(body.data.attribute).not.toBeNull();

  const attribute = body.data.attribute;
  
  expect(attribute.id).toBeDefined();
  expect(attribute._id).toBeDefined();
  expect(attribute.code).toBeDefined();
  expect(attribute.adminName).toBeDefined();
  expect(attribute.type).toBeDefined();
  
  expect(typeof attribute._id === 'string' || typeof attribute._id === 'number').toBeTruthy();
  
  if (attribute.isRequired !== undefined) {
    expect(typeof attribute.isRequired).toBe('boolean');
  }
  if (attribute.isConfigurable !== undefined) {
    expect(typeof attribute.isConfigurable).toBe('boolean');
  }
};

export const assertGetAttributeByIdNotFound = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attribute');
  expect(body.data.attribute).toBeNull();
};

export const assertAttributeByIdFullNode = (attribute: any) => {
  expect(attribute).not.toBeNull();

  expect(attribute.id).toBeDefined();
  expect(typeof attribute.id).toBe('string');
  expect(attribute.id.length).toBeGreaterThan(0);

  expect(attribute._id).toBeDefined();
  expect(typeof attribute._id === 'string' || typeof attribute._id === 'number').toBeTruthy();

  expect(attribute.code).toBeDefined();
  expect(typeof attribute.code).toBe('string');
  expect(attribute.code.length).toBeGreaterThan(0);

  expect(attribute.adminName).toBeDefined();
  expect(typeof attribute.adminName).toBe('string');
  expect(attribute.adminName.length).toBeGreaterThan(0);

  expect(attribute.type).toBeDefined();
  expect(typeof attribute.type === 'string' || typeof attribute.type === 'number').toBeTruthy();

  if (attribute.isRequired !== undefined) {
    expect(typeof attribute.isRequired).toBe('boolean');
  }
  if (attribute.isUnique !== undefined) {
    expect(typeof attribute.isUnique).toBe('boolean');
  }
  if (attribute.isFilterable !== undefined) {
    expect(typeof attribute.isFilterable).toBe('boolean');
  }
  if (attribute.isComparable !== undefined) {
    expect(typeof attribute.isComparable).toBe('boolean');
  }
  if (attribute.isConfigurable !== undefined) {
    expect(typeof attribute.isConfigurable).toBe('boolean');
  }
  if (attribute.isUserDefined !== undefined) {
    expect(typeof attribute.isUserDefined).toBe('boolean');
  }
  if (attribute.isVisibleOnFront !== undefined) {
    expect(typeof attribute.isVisibleOnFront).toBe('boolean');
  }
  if (attribute.valuePerLocale !== undefined) {
    expect(typeof attribute.valuePerLocale).toBe('boolean');
  }
  if (attribute.valuePerChannel !== undefined) {
    expect(typeof attribute.valuePerChannel).toBe('boolean');
  }
  if (attribute.enableWysiwyg !== undefined) {
    expect(typeof attribute.enableWysiwyg).toBe('boolean');
  }

  expect(attribute.position).toBeDefined();
  expect(typeof attribute.position).toBe('number');

  expect(attribute.createdAt).toBeDefined();
  expect(typeof attribute.createdAt).toBe('string');

  expect(attribute.updatedAt).toBeDefined();
  expect(typeof attribute.updatedAt).toBe('string');

  if (attribute.swatchType !== undefined && attribute.swatchType !== null) {
    expect(typeof attribute.swatchType).toBe('string');
  }

  if (attribute.validation !== undefined && attribute.validation !== null) {
    expect(typeof attribute.validation).toBe('string');
  }

  if (attribute.regex !== undefined && attribute.regex !== null) {
    expect(typeof attribute.regex).toBe('string');
  }

  if (attribute.defaultValue !== undefined && attribute.defaultValue !== null) {
    expect(typeof attribute.defaultValue).toBe('string');
  }

  if (attribute.columnName !== undefined && attribute.columnName !== null) {
    expect(typeof attribute.columnName).toBe('string');
  }

  if (attribute.validations !== undefined && attribute.validations !== null) {
    expect(typeof attribute.validations).toBe('string');
  }

  if (attribute.options !== undefined) {
    expect(attribute.options).toBeDefined();
    expect(attribute.options).toHaveProperty('edges');
    expect(attribute.options).toHaveProperty('pageInfo');
    expect(attribute.options).toHaveProperty('totalCount');
    expect(typeof attribute.options.totalCount).toBe('number');
    expect(attribute.options.totalCount).toBeGreaterThanOrEqual(0);

    expect(Array.isArray(attribute.options.edges)).toBeTruthy();
    attribute.options.edges.forEach((edge: any) => {
      expect(edge.node).toBeDefined();
      expect(edge.node.id).toBeDefined();
      expect(edge.node._id).toBeDefined();
      expect(edge.node.adminName).toBeDefined();
      expect(edge.node.sortOrder).toBeDefined();

      if (edge.node.swatchValue !== undefined) {
        expect(typeof edge.node.swatchValue === 'string' || edge.node.swatchValue === null).toBeTruthy();
      }

      if (edge.node.swatchValueUrl !== undefined) {
        expect(typeof edge.node.swatchValueUrl === 'string' || edge.node.swatchValueUrl === null).toBeTruthy();
      }

      if (edge.node.translation !== undefined && edge.node.translation !== null) {
        expect(edge.node.translation).toHaveProperty('id');
        expect(edge.node.translation).toHaveProperty('_id');
        expect(edge.node.translation).toHaveProperty('attributeOptionId');
        expect(edge.node.translation).toHaveProperty('locale');
        expect(edge.node.translation).toHaveProperty('label');
      }

      if (edge.node.translations !== undefined) {
        expect(edge.node.translations).toHaveProperty('edges');
        expect(edge.node.translations).toHaveProperty('pageInfo');
        expect(edge.node.translations).toHaveProperty('totalCount');

        if (edge.node.translations.edges.length > 0) {
          edge.node.translations.edges.forEach((transEdge: any) => {
            expect(transEdge.node).toBeDefined();
            expect(transEdge.node.id).toBeDefined();
            expect(transEdge.node._id).toBeDefined();
            expect(transEdge.node.attributeOptionId).toBeDefined();
            expect(transEdge.node.locale).toBeDefined();
            expect(transEdge.node.label).toBeDefined();
          });
        }

        expect(edge.node.translations.pageInfo).toBeDefined();
        expect(edge.node.translations.pageInfo.endCursor).toBeDefined();
        expect(edge.node.translations.pageInfo.startCursor).toBeDefined();
        expect(typeof edge.node.translations.pageInfo.hasNextPage).toBe('boolean');
        expect(typeof edge.node.translations.pageInfo.hasPreviousPage).toBe('boolean');
      }

      expect(edge.cursor).toBeDefined();
    });

    expect(attribute.options.pageInfo).toBeDefined();
    expect(attribute.options.pageInfo.endCursor).toBeDefined();
    expect(attribute.options.pageInfo.startCursor).toBeDefined();
    expect(typeof attribute.options.pageInfo.hasNextPage).toBe('boolean');
    expect(typeof attribute.options.pageInfo.hasPreviousPage).toBe('boolean');
  }

  if (attribute.translations !== undefined) {
    expect(attribute.translations).toBeDefined();
    expect(attribute.translations).toHaveProperty('edges');
    expect(attribute.translations).toHaveProperty('pageInfo');
    expect(attribute.translations).toHaveProperty('totalCount');
    expect(typeof attribute.translations.totalCount).toBe('number');
    expect(attribute.translations.totalCount).toBeGreaterThanOrEqual(0);

    expect(Array.isArray(attribute.translations.edges)).toBeTruthy();
    if (attribute.translations.edges.length > 0) {
      attribute.translations.edges.forEach((edge: any) => {
        expect(edge.node).toBeDefined();
        expect(edge.node.id).toBeDefined();
        expect(edge.node._id).toBeDefined();
        expect(edge.node.attributeId).toBeDefined();
        expect(edge.node.locale).toBeDefined();
        expect(edge.node.name).toBeDefined();
        expect(edge.cursor).toBeDefined();
      });
    }

    expect(attribute.translations.pageInfo).toBeDefined();
    expect(attribute.translations.pageInfo.endCursor).toBeDefined();
    expect(attribute.translations.pageInfo.startCursor).toBeDefined();
    expect(typeof attribute.translations.pageInfo.hasNextPage).toBe('boolean');
    expect(typeof attribute.translations.pageInfo.hasPreviousPage).toBe('boolean');
  }
};

export const assertGetAttributeByIdFullResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attribute');
  expect(body.data.attribute).not.toBeNull();

  assertAttributeByIdFullNode(body.data.attribute);
};

export const assertGetAttributeByIdWithOptions = (body: any) => {
  assertGetAttributeByIdFullResponse(body);

  const attribute = body.data.attribute;
  expect(attribute.options).toBeDefined();
  expect(Array.isArray(attribute.options.edges)).toBeTruthy();
};

export const assertGetAttributeByIdWithTranslations = (body: any) => {
  assertGetAttributeByIdFullResponse(body);

  const attribute = body.data.attribute;
  expect(attribute.translations).toBeDefined();
  expect(Array.isArray(attribute.translations.edges)).toBeTruthy();
};

export const assertAttributeOptionNode = (option: any) => {
  expect(option).not.toBeNull();

  expect(option.id).toBeDefined();
  expect(typeof option.id).toBe('string');
  expect(option.id.length).toBeGreaterThan(0);

  expect(option._id).toBeDefined();
  expect(typeof option._id === 'string' || typeof option._id === 'number').toBeTruthy();

  expect(option.adminName).toBeDefined();
  expect(typeof option.adminName).toBe('string');

  expect(option.sortOrder).toBeDefined();
  expect(typeof option.sortOrder).toBe('number');

  if (option.swatchValue !== undefined && option.swatchValue !== null) {
    expect(typeof option.swatchValue).toBe('string');
  }
};

export const assertAttributeOptionsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributeOptions');
  expect(body.data.attributeOptions).not.toBeNull();

  const options = body.data.attributeOptions;

  expect(options).toHaveProperty('edges');
  expect(Array.isArray(options.edges)).toBeTruthy();

  options.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    assertAttributeOptionNode(edge.node);
  });

  expect(options.pageInfo).toBeDefined();
  expect(options.pageInfo).toHaveProperty('hasNextPage');
  expect(options.pageInfo).toHaveProperty('endCursor');
  expect(typeof options.pageInfo.hasNextPage).toBe('boolean');
};

export const assertAttributeOptionsWithTotalCount = (body: any) => {
  assertAttributeOptionsResponse(body);

  const options = body.data.attributeOptions;
  expect(options).toHaveProperty('totalCount');
  expect(typeof options.totalCount).toBe('number');
  expect(options.totalCount).toBeGreaterThanOrEqual(0);
};

export const assertAttributeOptionWithTranslationsNode = (option: any) => {
  expect(option).not.toBeNull();

  expect(option.id).toBeDefined();
  expect(option.adminName).toBeDefined();
  expect(option.sortOrder).toBeDefined();

  if (option.translations !== undefined) {
    expect(option.translations).toHaveProperty('edges');
    expect(Array.isArray(option.translations.edges)).toBeTruthy();

    if (option.translations.edges.length > 0) {
      option.translations.edges.forEach((edge: any) => {
        expect(edge.node.locale).toBeDefined();
        expect(edge.node.label).toBeDefined();
      });
    }
  }
};

export const assertAttributeOptionsWithTranslationsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributeOptions');
  expect(body.data.attributeOptions).not.toBeNull();

  const options = body.data.attributeOptions;
  expect(options).toHaveProperty('edges');
  expect(Array.isArray(options.edges)).toBeTruthy();

  options.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    assertAttributeOptionWithTranslationsNode(edge.node);
  });
};

export const assertSwatchOptionNode = (option: any) => {
  expect(option).not.toBeNull();

  expect(option.id).toBeDefined();
  expect(option.adminName).toBeDefined();

  if (option.swatchValue !== undefined && option.swatchValue !== null) {
    expect(typeof option.swatchValue).toBe('string');
  }

  if (option.swatchValueUrl !== undefined && option.swatchValueUrl !== null) {
    expect(typeof option.swatchValueUrl).toBe('string');
  }

  if (option.translation !== undefined && option.translation !== null) {
    expect(option.translation.locale).toBeDefined();
    expect(option.translation.label).toBeDefined();
  }
};

export const assertSwatchOptionsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributeOptions');
  expect(body.data.attributeOptions).not.toBeNull();

  const options = body.data.attributeOptions;
  expect(options).toHaveProperty('edges');
  expect(Array.isArray(options.edges)).toBeTruthy();

  options.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    assertSwatchOptionNode(edge.node);
  });
};

export const assertAttributeOptionByIdNode = (option: any) => {
  expect(option).not.toBeNull();

  expect(option.id).toBeDefined();
  expect(typeof option.id).toBe('string');
  expect(option.id.length).toBeGreaterThan(0);

  expect(option._id).toBeDefined();
  expect(typeof option._id === 'string' || typeof option._id === 'number').toBeTruthy();

  expect(option.adminName).toBeDefined();
  expect(typeof option.adminName).toBe('string');

  expect(option.sortOrder).toBeDefined();
  expect(typeof option.sortOrder).toBe('number');

  if (option.swatchValue !== undefined) {
    expect(typeof option.swatchValue === 'string' || option.swatchValue === null).toBeTruthy();
  }
  if (option.swatchValueUrl !== undefined) {
    expect(typeof option.swatchValueUrl === 'string' || option.swatchValueUrl === null).toBeTruthy();
  }

  if (option.translation !== undefined && option.translation !== null) {
    expect(option.translation.id).toBeDefined();
    expect(option.translation._id).toBeDefined();
    expect(option.translation.attributeOptionId).toBeDefined();
    expect(option.translation.locale).toBeDefined();
    expect(option.translation.label).toBeDefined();
  }

  if (option.translations !== undefined) {
    expect(option.translations).toHaveProperty('edges');
    expect(option.translations).toHaveProperty('pageInfo');
    expect(option.translations).toHaveProperty('totalCount');

    expect(Array.isArray(option.translations.edges)).toBeTruthy();

    if (option.translations.edges.length > 0) {
      option.translations.edges.forEach((edge: any) => {
        expect(edge.node.id).toBeDefined();
        expect(edge.node._id).toBeDefined();
        expect(edge.node.attributeOptionId).toBeDefined();
        expect(edge.node.locale).toBeDefined();
        expect(edge.node.label).toBeDefined();
      });
    }

    expect(option.translations.pageInfo.endCursor).toBeDefined();
    expect(option.translations.pageInfo.startCursor).toBeDefined();
    expect(typeof option.translations.pageInfo.hasNextPage).toBe('boolean');
    expect(typeof option.translations.pageInfo.hasPreviousPage).toBe('boolean');
  }
};

export const assertGetAttributeOptionByIdResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributeOption');
  expect(body.data.attributeOption).not.toBeNull();

  assertAttributeOptionByIdNode(body.data.attributeOption);
};

export const assertAttributeOptionsPaginatedResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributeOptions');
  expect(body.data.attributeOptions).not.toBeNull();

  const options = body.data.attributeOptions;

  expect(options).toHaveProperty('edges');
  expect(Array.isArray(options.edges)).toBeTruthy();

  options.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    expect(edge.node.id).toBeDefined();
    expect(edge.node.adminName).toBeDefined();
    expect(edge.node.sortOrder).toBeDefined();
    expect(edge.cursor).toBeDefined();
  });

  expect(options.pageInfo).toBeDefined();
  expect(options.pageInfo.hasNextPage).toBeDefined();
  expect(options.pageInfo.endCursor).toBeDefined();
  expect(options.pageInfo.hasPreviousPage).toBeDefined();
  expect(options.pageInfo.startCursor).toBeDefined();
  expect(typeof options.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof options.pageInfo.hasPreviousPage).toBe('boolean');
};

export const assertAttributeWithOptionsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attribute');
  expect(body.data.attribute).not.toBeNull();

  const attribute = body.data.attribute;

  expect(attribute.id).toBeDefined();
  expect(attribute.code).toBeDefined();
  expect(attribute.adminName).toBeDefined();

  expect(attribute.options).toBeDefined();
  expect(attribute.options).toHaveProperty('edges');
  expect(Array.isArray(attribute.options.edges)).toBeTruthy();

  attribute.options.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    expect(edge.node.id).toBeDefined();
    expect(edge.node.adminName).toBeDefined();
    expect(edge.node.sortOrder).toBeDefined();
    expect(edge.cursor).toBeDefined();

    if (edge.node.swatchValue !== undefined) {
      expect(typeof edge.node.swatchValue).toBe('string');
    }
    if (edge.node.translation !== undefined && edge.node.translation !== null) {
      expect(edge.node.translation.locale).toBeDefined();
      expect(edge.node.translation.label).toBeDefined();
    }
  });

  expect(attribute.options.pageInfo).toBeDefined();
  expect(attribute.options.pageInfo.hasNextPage).toBeDefined();
  expect(attribute.options.pageInfo.endCursor).toBeDefined();
};

export const assertColorOptionsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributeOptions');
  expect(body.data.attributeOptions).not.toBeNull();

  const options = body.data.attributeOptions;
  expect(options).toHaveProperty('edges');
  expect(Array.isArray(options.edges)).toBeTruthy();

  options.edges.forEach((edge: any) => {
    expect(edge.node).toBeDefined();
    expect(edge.node.adminName).toBeDefined();

    if (edge.node.swatchValue !== undefined && edge.node.swatchValue !== null) {
      expect(typeof edge.node.swatchValue).toBe('string');
    }

    if (edge.node.translation !== undefined && edge.node.translation !== null) {
      expect(edge.node.translation.label).toBeDefined();
    }
  });
};

export const assertAttributeOptionNotFound = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('attributeOption');
  expect(body.data.attributeOption).toBeNull();
};
