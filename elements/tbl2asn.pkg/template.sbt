Submit-block ::= {
  contact {
    contact {
      name name {
        last "{%LastName%}",
        first "{%FirstName%}",
        middle "{%MiddleInitial%}",
        initials "{%Initials%}",
        suffix "",
        title ""
      },
      affil std {
        affil "{%Organization%}",
        div "{%Department%}",
        city "{%City%}",
        sub "{%StateProvince%}",
        country "{%Country%}",
        street "{%AddressStreet%}",
        email "{%Email%}",
        fax "{%Fax%}",
        phone "{%Phone%}",
        postal-code "{%PostalCode%}"
      }
    }
  },
  cit {
    authors {
      names std {
        {
          name name {
              last "{%LastName%}" ,
              first "{%FirstName%}" ,
              middle "{%MiddleInitial%}",
              initials "{%Initials%}",
              suffix "",
              title ""
          }
        }
      },
      affil std {
        affil "{%Organization%}",
        div "{%Department%}",
        city "{%City%}",
        sub "{%StateProvince%}",
        country "{%Country%}",
        street "{%AddressStreet%}",
        postal-code "{%PostalCode%}"
      }
    }
  },
  subtype new
}
Seqdesc ::= pub {
  pub {
    gen {
      cit "{%PublicationStatus%}",
      authors {
        names std {
          {%PublicationAuthors%}
        }
      },
      title "{%PublicationTitle%}"
    }
  }
}
Seqdesc ::= user {
  type str "StructuredComment",
  data {
    {
      label str "StructuredCommentPrefix",
      data str "##Assembly-Data-START##"
    },
    {
      label str "StructuredCommentSuffix",
      data str "##Assembly-Data-END##"
    },
    {
      label str "Assembly Method",
      data str ""
    },
    {
      label str "Sequencing Technology",
      data str "{%SequencingTechnology%}"
    }
  }
}
Seqdesc ::= user {
  type str "StructuredComment",
  data {
    {
      label str "StructuredCommentPrefix",
      data str "##SymbiotaSpecimenReference-START##"
    },
    {
      label str "StructuredCommentSuffix",
      data str "##SymbiotaSpecimenReference-END##"
    },
    {
      label str "Source Record URL",
      data str "{%PortalUrl%}"
    },
    {
      label str "Occurrence ID (GUID)",
      data str "{%OccurrenceId%}"
    },
    {
      label str "Record ID",
      data str "{%RecordId%}"
    },
    {
      label str "Institution Code",
      data str "{%InstitutionCode%}"
    },
    {
      label str "Collection Code",
      data str "{%CollectionCode%}"
    },
    {
      label str "Catalog Number",
      data str "{%CatalogNumber%}"
    },
    {
      label str "Other Catalog Numbers",
      data str "{%OtherCatalogNumbers%}"
    },
  }
}
